# Copilot Instructions

Following, the is common standard rules you have to apply in all projects.

And each project have its own set of rules, dispatcher in this files:

**Applications principales :**
- [api](/docs/api/copilot-instructions.md) - API REST Laravel
- [app](/docs/app/copilot-instructions.md) - Application Laravel avec OAuth2/Passport
- [auth](/docs/auth/copilot-instructions.md) - Application d'authentification avec Inertia.js
- [notifications](/docs/notifications/copilot-instructions.md) - Service de notifications multi-canal

**Packages et SDKs :**
- [api-oauth2-sdk](/docs/api-oauth2-sdk/copilot-instructions.md) - Package PHP OAuth2 client
- [api-sdk](/docs/api-sdk/copilot-instructions.md) - SDK PHP pour APIs du monorepo
- [tailwindui](/docs/tailwindui/copilot-instructions.md) - Design system avec composants Catalyst

**Sites statiques :**
- [abenevaut](/docs/abenevaut/copilot-instructions.md) - Portfolio personnel avec Docker
- [ai-prompt](/docs/ai-prompt/copilot-instructions.md) - Site showcase prompts IA
- [metronome](/docs/metronome/copilot-instructions.md) - Application métronome avec Web Audio API

**Projets éducatifs :**
- [laravel-france-articles](/docs/laravel-france-articles/copilot-instructions.md) - Articles techniques Laravel
- [laravel-france-bootcamp](/docs/laravel-france-bootcamp/copilot-instructions.md) - Ressources pédagogiques Laravel
- [cybersecurity-challenges](/docs/cybersecurity-challenges/copilot-instructions.md) - Writeups CTF et challenges
- [cybersecurity-cybermois](/docs/cybersecurity-cybermois/copilot-instructions.md) - Challenges éducatifs cybersécurité

**Projets spécialisés :**
- [mailjet](/docs/mailjet/copilot-instructions.md) - Tests API Mailjet
- [workflow](/docs/workflow/copilot-instructions.md) - Configurations Docker Compose et intégrations API
- [video](/docs/video/copilot-instructions.md) - Projets After Effects
- [docker-n8n](/docs/docker-n8n/copilot-instructions.md) - Configuration n8n avec tests RSpec
- [maskot](/docs/maskot/copilot-instructions.md) - Packages npm de ressources images et logos

## AI Behavior Guidelines

### Factual Analysis First
- **ALWAYS** analyze existing code before proposing new solutions
- **ALWAYS** base responses on actual implementation found in the codebase
- **NEVER** assume functionality that doesn't exist in the repository
- **ALWAYS** verify claims by examining the actual files and structure

### Code-Based Problem Solving
- **ALWAYS** attempt to solve problems using existing patterns and code
- **ALWAYS** look for similar implementations within the monorepo
- **ALWAYS** prioritize consistency with existing architecture
- **ONLY** propose new solutions when existing code cannot be adapted

### Conflict Resolution
- **ALWAYS** identify when existing code conflicts with standards, performance, or security rules
- **ALWAYS** clearly explain the conflict and its implications
- **ALWAYS** propose comprehensive fixes that address the root cause
- **NEVER** apply band-aid solutions that mask underlying issues

### Context Awareness
- **ALWAYS** focus on the specific context being edited
- **NEVER** remove or modify code outside the current editing scope
- **NEVER** delete "unused" code that may be used elsewhere or reserved for future use
- **ALWAYS** ask for clarification when the scope of changes is unclear

### Progressive Enhancement
- **ALWAYS** start with minimal changes that solve the immediate problem
- **ALWAYS** explain why existing code should be modified
- **ALWAYS** provide step-by-step reasoning for proposed changes
- **NEVER** over-engineer solutions beyond the stated requirements

### Architecture Decision Records
- **ALWAYS** create an ADR when making architectural or significant design changes
- **ALWAYS** name ADR files as `/docs/[project]/ADR/[current-date-modification-title].md`
- **ALWAYS** use format: `YYYY-MM-DD-modification-title` (e.g., `2025-07-12-implement-oauth2-authentication`)
- **ALWAYS** document the context, decision, and consequences in the ADR
- **ALWAYS** include impact analysis and rationale for the change
- **ALWAYS** link ADR to its corresponding Pull Request number
- **ALWAYS** define clear objectives and success criteria in the ADR
- **ALWAYS** reference the ADR in the Pull Request description

### Communication Style
- **ALWAYS** adopt a thoughtful and reflective tone
- **ALWAYS** be objective, concise, and pragmatic
- **ALWAYS** focus on practical solutions over theoretical discussions
- **NEVER** use excessive explanations when simple answers suffice
- **ALWAYS** prioritize clarity and actionable information

## Key Principles

### Design System Compliance
- **ALWAYS** respect and use @abenevaut/tailwindui design system
- **NEVER** create custom components that duplicate design system functionality
- **ALWAYS** import from `@abenevaut/tailwindui/src/js/Catalyst/[component]`
- Follow naming convention: PascalCase for components

```javascript
// Import example
import { ExampleComponent } from '@abenevaut/tailwindui/src/js/Catalyst/example-component'
```

## Development Standards

### Frontend Development Standards

#### Build Configuration
- **ALWAYS** use Vite as bundler with appropriate plugins
- **ALWAYS** configure proper asset versioning with hashes
- **ALWAYS** enable sourcemaps in development only
- **ALWAYS** minify assets in production

#### React Component Standards (for JS projects)
- **ALWAYS** use functional components with hooks
- **ALWAYS** import from @abenevaut/tailwindui design system
- **NEVER** create custom UI components that exist in design system
- **ALWAYS** use JSX file extension (.jsx)

#### Performance Standards
- **ALWAYS** minimize bundle size with tree-shaking
- **ALWAYS** lazy load non-critical components
- **ALWAYS** optimize images for web delivery

## Security Standards

### Content Security
- **NEVER** include sensitive data in frontend bundle
- **ALWAYS** validate external content sources
- **ALWAYS** sanitize user-generated content

### Environment Configuration
- **ALWAYS** use environment-specific configs
- **NEVER** commit sensitive data to repository
- **ALWAYS** validate configuration before deployment

## Code Quality Standards

### File Organization
- **ALWAYS** organize files in logical folder structures
- **ALWAYS** use descriptive file names
- **ALWAYS** keep related files grouped together
- **ALWAYS** separate concerns appropriately

### Documentation
- **ALWAYS** document complex logic and decisions
- **ALWAYS** provide clear examples in documentation
- **ALWAYS** maintain up-to-date README files
- **ALWAYS** explain acronyms and technical terms

### Version Control
- **ALWAYS** use descriptive commit messages
- **ALWAYS** maintain clean git history
- **ALWAYS** review code before merging
- **ALWAYS** test changes before committing
