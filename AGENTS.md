# AGENTS.md — BindingEngine Demo Application

## README.md

The README.md contains valuable information about the structure and purpose of this repository and MUST be read and followed before making changes.

---

# Purpose

This repository contains a minimal demonstration application for the Consolidated Witchcraft BindingEngine ecosystem.

Its purpose is to:

- demonstrate end-to-end library integration
- validate ecosystem ergonomics
- provide executable documentation
- provide reproducible example pipelines
- act as a lightweight manual testing environment

This repository is intentionally small.

It is NOT:
- a production application
- a reference architecture for deployment
- a full editor implementation
- a framework starter kit
- a persistence layer
- a graph database implementation

The focus is:
> demonstrating semantic pipeline behaviour clearly and deterministically.

---

# Architectural Principles

## Minimalism First

This repository should remain intentionally lightweight.

Avoid:
- unnecessary abstraction
- framework sprawl
- infrastructure complexity
- premature optimisation
- application-specific architecture

The demo should be understandable quickly.

---

## End-To-End Clarity

The primary purpose of the repository is to demonstrate the semantic pipeline:

```text
Markdown
↓
Parser
↓
Vocabulary Validation
↓
Assertions
↓
Projection
↓
Serialization
```

Code should optimise for:
- readability
- traceability
- explicit data flow

Avoid:
- hidden magic
- container-heavy indirection
- runtime discovery systems

---

## Provenance Must Remain Visible

The ecosystem is provenance-aware.

The demo application MUST preserve and expose provenance information throughout the pipeline.

Do not:
- discard source context
- strip source spans
- remove vocabulary versioning
- flatten semantic identity unnecessarily

The output should clearly demonstrate provenance preservation.

---

## Determinism Is Mandatory

Given identical:
- markdown input
- vocabulary input
- library versions

the same output MUST be produced.

Avoid:
- timestamps
- random identifiers
- hidden mutable state
- nondeterministic ordering

unless explicitly required for demonstration purposes.

---

# Repository Standards

## PHP Standards

- `declare(strict_types=1);` is mandatory
- PHPStan MUST pass at the configured level
- All public APIs MUST be fully typed
- Avoid associative-array ambiguity where possible
- Prefer immutable value objects over mutable structures

---

## Exceptions

Exceptions MUST:
- preserve previous exceptions
- contain actionable contextual information
- remain domain-specific where appropriate

Never throw:
- `\Exception`
- `\RuntimeException`
- `\Throwable`

except at application boundaries.

---

## Testing Standards

This repository SHOULD contain:
- integration-style tests
- pipeline verification tests
- serialization verification tests

The goal of tests is:
> proving ecosystem interoperability.

Tests SHOULD verify:
- parser integration
- vocabulary validation
- assertion extraction
- projection extraction
- serialization output

---

## CLI Design

The demo application is expected to expose:
- simple commands
- predictable output
- explicit inputs

Prefer:
```bash
php bin/demo document.md vocabulary.json
```

over:
- interactive flows
- hidden configuration
- environment-driven behaviour

---

## Serialization Output

Serialized output SHOULD:
- remain deterministic
- preserve provenance
- preserve projection identity
- remain transport-safe

JSON output SHOULD use:
- `JSON_PRETTY_PRINT`
- `JSON_THROW_ON_ERROR`

for clarity and correctness.

---

## Dependency Discipline

This repository exists to demonstrate the BindingEngine ecosystem.

Avoid introducing:
- unnecessary runtime dependencies
- frontend frameworks
- databases
- queues
- ORM layers
- web frameworks

unless the repository purpose intentionally changes.

---

## Preferred Design Style

Prefer:
- small focused commands
- explicit orchestration
- immutable data structures
- direct library composition
- deterministic transforms

Avoid:
- service locators
- hidden global state
- runtime mutation
- excessive framework ceremony

---

## Commit Standards

Commits MUST:
- pass the full test suite
- pass PHPStan
- preserve deterministic behaviour
- preserve provenance visibility

Do not commit:
- debugging artefacts
- dead code
- partially wired integrations
- failing pipelines

---

## Long-Term Direction

This repository is intended to become:
- executable ecosystem documentation
- a semantic pipeline reference implementation
- a lightweight interoperability testbed

Optimise for:
- clarity
- traceability
- correctness
- maintainability

over:
- convenience
- cleverness
- abstraction-heavy architecture

---

## Coding Standards

Coding standards are contained within the `./codingstandards/` subdirectory and MUST be followed.