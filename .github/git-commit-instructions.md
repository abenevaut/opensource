All PRs have to follow [Conventional Commits v1.0.x](https://www.conventionalcommits.org/en/v1.0.0/).

A Git commit message must use this format:

```text
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

- Types and scopes are defined as follows:
    - feat or feat(<SCOPE>) for a feature
    - fix or fix(<SCOPE>) for a bug fix or hotfix
    - chore or chore(<SCOPE>) for maintenance tasks
    - Scopes (<SCOPE>) are:
        - names of root projects, like: "adguard-blocklists", "bastion", "docker-kata" etc...
        - scope is optional for cross-cutting changes (tooling, GitHub configuration, documentation, linters, test infrastructure), when not directly tied to the application code
- After the scope, the description must start with an action verb, in present imperative and lowercase, from this list: add, update, remove, rollback.
    - Example: feat(blog): add new action to set ...
    - Example: chore: remove expired documentation
- The [optional body] section can be used to provide additional context.
- If the commit introduces a breaking change, end the message with a dedicated footer in [optional footer(s)]:
    - Example:
        - feat(blog): update dedicated workflow
        - BREAKING CHANGE: method to retrieve responsibleNickname() in PostsService no longer exists
    - Multiple BREAKING CHANGE footers can be added when needed.
