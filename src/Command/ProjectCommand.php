<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Demo\Command;

use ConsolidatedWitchcraft\BindingEngine\Assertions\AstAssertionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Conflicts\ConflictDetector;
use ConsolidatedWitchcraft\BindingEngine\Conflicts\RuleLoading\JsonConflictRuleLoader;
use ConsolidatedWitchcraft\BindingEngine\Conflicts\Rules\RelationshipCardinalityConflictRuleDefinition;
use ConsolidatedWitchcraft\BindingEngine\Parser\Parser;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\CompositeProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\EntityProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\RelationshipProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\ProjectionSetArraySerializer;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Validator;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\JsonVocabularyLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'binding:project',
    description: 'Parse, validate, assert, project, detect conflicts and serialize a BindingEngine document.',
)]
final class ProjectCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'document',
                InputArgument::REQUIRED,
                'Path to the markdown document.',
            )
            ->addArgument(
                'vocabulary',
                InputArgument::REQUIRED,
                'Path to the vocabulary JSON file.',
            )
            ->addArgument(
                'conflictRules',
                InputArgument::OPTIONAL,
                'Optional path to a conflict rules JSON file.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $documentPath = $input->getArgument('document');
        $vocabularyPath = $input->getArgument('vocabulary');
        $conflictRulesPath = $input->getArgument('conflictRules');

        if (
            !is_string($documentPath)
            || !is_string($vocabularyPath)
            || ($conflictRulesPath !== null && !is_string($conflictRulesPath))
        ) {
            $io->error('Invalid command arguments.');

            return Command::FAILURE;
        }

        if (!is_file($documentPath)) {
            $io->error(sprintf(
                'Document file not found: %s',
                $documentPath,
            ));

            return Command::FAILURE;
        }

        if (!is_file($vocabularyPath)) {
            $io->error(sprintf(
                'Vocabulary file not found: %s',
                $vocabularyPath,
            ));

            return Command::FAILURE;
        }

        if ($conflictRulesPath !== null && !is_file($conflictRulesPath)) {
            $io->error(sprintf(
                'Conflict rules file not found: %s',
                $conflictRulesPath,
            ));

            return Command::FAILURE;
        }

        $io->section('Loading input files');

        $documentSource = file_get_contents($documentPath);
        $vocabularySource = file_get_contents($vocabularyPath);
        $conflictRulesSource = null;

        if ($conflictRulesPath !== null) {
            $conflictRulesSource = file_get_contents($conflictRulesPath);
        }

        if (
            $documentSource === false
            || $vocabularySource === false
            || ($conflictRulesPath !== null && $conflictRulesSource === false)
        ) {
            $io->error('Failed to read input files.');

            return Command::FAILURE;
        }

        $io->success('Input files loaded successfully.');

        $parser = new Parser();
        $vocabularyLoader = new JsonVocabularyLoader();
        $assertionExtractor = new AstAssertionExtractor();

        $projectionExtractor = new CompositeProjectionExtractor([
            new EntityProjectionExtractor(),
            new RelationshipProjectionExtractor(),
        ]);

        $projectionSerializer = new ProjectionSetArraySerializer();

        $io->section('Loading vocabulary');

        $vocabulary = $vocabularyLoader->load($vocabularySource);

        $io->success(sprintf(
            'Loaded vocabulary "%s@%s".',
            $vocabulary->getIdentifier(),
            $vocabulary->getVersion(),
        ));

        $io->section('Parsing markdown document');

        $parseResult = $parser->parse($documentSource);

        if ($parseResult->hasErrors()) {
            $io->error('Document contains parser errors.');

            $io->table(
                ['Code', 'Message'],
                array_map(
                    static fn ($diagnostic): array => [
                        $diagnostic->getCode(),
                        $diagnostic->getMessage(),
                    ],
                    $parseResult->getDiagnostics(),
                ),
            );

            return Command::FAILURE;
        }

        $io->success('Document parsed successfully.');

        $io->section('Validating document');

        $validator = new Validator($vocabulary);

        $validationResult = $validator->validate(
            $parseResult->getDocument(),
        );

        if ($validationResult->hasErrors()) {
            $io->error('Document failed vocabulary validation.');

            $io->table(
                ['Code', 'Message'],
                array_map(
                    static fn ($diagnostic): array => [
                        $diagnostic->getCode(),
                        $diagnostic->getMessage(),
                    ],
                    $validationResult->getDiagnostics(),
                ),
            );

            return Command::FAILURE;
        }

        $io->success('Vocabulary validation succeeded.');

        $io->section('Extracting assertions');

        $sourceContext = new SourceContext(
            sourceId: 'demo',
            documentId: basename($documentPath),
            revisionId: 'demo-revision',
            vocabularyIdentifier: $vocabulary->getIdentifier(),
            vocabularyVersion: $vocabulary->getVersion(),
        );

        $assertionSet = $assertionExtractor->extract(
            document: $parseResult->getDocument(),
            sourceContext: $sourceContext,
        );

        $io->success(sprintf(
            'Extracted %d assertion(s).',
            $assertionSet->count(),
        ));

        $io->section('Projecting semantic structures');

        $projectionSet = $projectionExtractor->extract(
            assertionSet: $assertionSet,
        );

        $io->success(sprintf(
            'Produced %d projection(s).',
            $projectionSet->count(),
        ));

        $serializedConflicts = null;

        if ($conflictRulesSource !== null) {
            $io->section('Loading conflict rules');

            $conflictRuleLoader = new JsonConflictRuleLoader([
                new RelationshipCardinalityConflictRuleDefinition(),
            ]);

            $conflictRules = $conflictRuleLoader->load($conflictRulesSource);

            $io->success(sprintf(
                'Loaded %d conflict rule(s).',
                count($conflictRules),
            ));

            $io->section('Detecting semantic conflicts');

            $conflictDetector = new ConflictDetector($conflictRules);

            $conflictSet = $conflictDetector->detect($projectionSet);

            $io->success(sprintf(
                'Detected %d conflict(s).',
                $conflictSet->count(),
            ));

            $serializedConflicts = [];

            foreach ($conflictSet->getConflicts() as $conflict) {
                $serializedConflicts[] = [
                    'conflictType' => $conflict->getConflictType()->value,
                    'severity' => $conflict->getSeverity()->value,
                    'subjectKey' => $conflict->getSubjectKey(),
                    'field' => $conflict->getField(),
                    'candidates' => array_map(
                        static fn ($candidate): array => [
                            'value' => $candidate->getValue(),
                            'projectionKey' => $candidate
                                ->getProjection()
                                ->getProjectionKey()
                                ->getValue(),
                        ],
                        $conflict->getCandidates(),
                    ),
                ];
            }
        }

        $io->section('Serializing projections');

        $serializedProjections = $projectionSerializer->serialize($projectionSet);

        $io->success('Projection serialization completed.');

        $outputPayload = [
            'projections' => $serializedProjections,
        ];

        if ($serializedConflicts !== null) {
            $outputPayload['conflicts'] = $serializedConflicts;
        }

        $io->section('Serialized Output');

        $io->writeln(json_encode(
            $outputPayload,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR,
        ));

        return Command::SUCCESS;
    }
}
