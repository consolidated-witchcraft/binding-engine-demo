# Binding Engine Demo

A minimal end-to-end demonstration application for the Consolidated Witchcraft BindingEngine ecosystem.

This repository demonstrates the complete semantic processing pipeline from authored markdown bindings through semantic projection and serialization.

## Purpose

The purpose of this repository is to:

- demonstrate ecosystem integration
- validate package interoperability
- provide executable documentation
- provide realistic integration examples
- act as a lightweight semantic pipeline testbed

This repository intentionally prioritises:

- clarity
- determinism
- provenance visibility
- explicit orchestration

over:

- framework complexity
- infrastructure concerns
- production application architecture

---

# Semantic Pipeline

The demo application exercises the complete BindingEngine pipeline:

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

The resulting output demonstrates:

- semantic extraction
- provenance preservation
- deterministic projection
- semantic identity generation
- transport-safe serialization

---

# Installation

## Requirements

- PHP 8.4+
- Composer

## Install Dependencies

```bash
composer install
```

---

# Example Usage

## Example Markdown Document

```markdown
@person[jane-austen](Jane Austen) was an English writer known primarily for her six novels, which implicitly interpret, critique, and comment on the English landed gentry at the end of the 18th century.

Jane Austen was born on 16 December 1775 in @place[steventon-hampshire](Steventon, Hampshire). Her @relationship[type: parent_of, subject: george-austen, object: jane-austen](father, George Austen), wrote of her arrival in a letter that her mother, @relationship[type: parent_of, subject: cassandra-austen, object: jane-austen](Cassandra), "certainly expected to have been brought to bed a month ago." He added that the newborn infant was "a present plaything for Cassy and a future companion." The winter of 1775-1776 was particularly harsh, and it was not until 5 April that she was baptised at the local church and christened Jane.
```

## Example Vocabulary

```json
{
  "identifier": "test-vocabulary",
  "label": "Test Vocabulary",
  "version": "0.2.0",
  "bindingTypes": [
    {
      "identifier": "person",
      "label": "Person",
      "description": "A person entity.",
      "allowedPayloadShapes": [
        "shorthand"
      ],
      "attributes": []
    },
    {
      "identifier": "place",
      "label": "Place",
      "description": "A place entity.",
      "allowedPayloadShapes": [
        "shorthand"
      ],
      "attributes": []
    },
    {
      "identifier": "relationship",
      "label": "Relationship",
      "description": "A relationship between two entities.",
      "allowedPayloadShapes": [
        "attribute_list"
      ],
      "attributes": [
        {
          "identifier": "type",
          "label": "Type",
          "description": "The relationship type.",
          "valueType": "string",
          "required": true,
          "repeatable": false,
          "role": "semantic"
        },
        {
          "identifier": "subject",
          "label": "Subject",
          "description": "The subject entity.",
          "valueType": "identifier",
          "required": true,
          "repeatable": false,
          "role": "semantic"
        },
        {
          "identifier": "object",
          "label": "Object",
          "description": "The object entity.",
          "valueType": "identifier",
          "required": true,
          "repeatable": false,
          "role": "semantic"
        }
      ]
    }
  ]
}
```

## Run the Demo

```bash
php bin/console binding:project examples/document.md examples/vocabulary.json
```

---

# Example Output

Example conceptual output:

```json
[
  {
    "projectionType": "entity",
    "projectionKey": "entity:person:jane-austen",
    "entityType": "person",
    "identifier": "jane-austen",
    "label": "Jane Austen"
  },
  {
    "projectionType": "entity",
    "projectionKey": "entity:place:steventon-hampshire",
    "entityType": "place",
    "identifier": "steventon-hampshire",
    "label": "Steventon, Hampshire"
  },
  {
    "projectionType": "relationship",
    "projectionKey": "relationship:parent_of:george-austen:jane-austen",
    "relationshipType": "parent_of",
    "subject": "george-austen",
    "object": "jane-austen",
    "label": "father, George Austen"
  },
  {
    "projectionType": "relationship",
    "projectionKey": "relationship:parent_of:cassandra-austen:jane-austen",
    "relationshipType": "parent_of",
    "subject": "cassandra-austen",
    "object": "jane-austen",
    "label": "Cassandra"
  }
]
```

Actual serialized output also includes:

- originating assertion data
- provenance information
- source spans
- vocabulary context

---

# Semantic vs Visibility Metadata

BindingEngine distinguishes between:

- semantic attributes
- metadata attributes
- visibility attributes
- system attributes

Semantic attributes contribute to semantic meaning and projection identity.

Visibility and metadata attributes are preserved through the pipeline without becoming part of semantic graph identity.

For example:

```markdown
@event[
    type: battle,
    occurred_at: reckoning:348-ashmoon-03,
    revealed_at: 2026-07-18
](Battle of Thornbridge Holt)
```

May use:

```json
{
  "identifier": "revealed_at",
  "role": "visibility"
}
```

This allows applications to implement:
- spoiler management
- campaign reveal systems
- publication scheduling
- access control

without contaminating semantic identity or graph structure.

---

# Repository Goals

This repository is intentionally minimal.

It is NOT intended to:

- provide a production application architecture
- act as a graph database
- provide a web interface
- provide an editor
- implement inference
- resolve canonical truth
- merge semantic identities

Its purpose is to demonstrate:

> deterministic semantic pipeline behaviour.

---

# Related Packages

| Package                                                  | Responsibility                                 |
|----------------------------------------------------------|------------------------------------------------|
| consolidated-witchcraft/binding-engine-parser            | Parses binding syntax into AST structures      |
| consolidated-witchcraft/binding-engine-vocabulary        | Defines semantic vocabulary rules              |
| consolidated-witchcraft/binding-engine-vocabulary-loader | Loads vocabularies from JSON definitions       |
| consolidated-witchcraft/binding-engine-assertions        | Extracts provenance-aware semantic assertions  |
| consolidated-witchcraft/binding-engine-projection        | Projects assertions into semantic structures   |

---

# Development

## Quality Standards

Commits should not be made without running:

```bash
composer test
composer stan
```

The repository enforces:

- strict typing
- deterministic behaviour
- provenance preservation
- explicit semantic modelling

---

# Design Philosophy

The BindingEngine ecosystem treats authored semantic bindings as:

> historical semantic claims

rather than:

> canonical objective truth

The demo application therefore preserves:

- provenance
- vocabulary context
- semantic identity
- authored structure

throughout the full pipeline.

---

# License

Licensed under the GNU Affero General Public License v3.0 or later (`AGPL-3.0-or-later`).