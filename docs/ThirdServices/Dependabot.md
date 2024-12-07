- documentation:
  - https://docs.github.com/en/code-security/getting-started/dependabot-quickstart-guide

## Dependabot

Project file `.github/dependabot.yml`.
Template to add a new project:

```yml
- package-ecosystem: "<PROJECT_ECOSYSTEM>"
  directory: "<PROJECT_DIRECTORY>"
  schedule:
    interval: monthly
    time: "06:00"
    timezone: Europe/Paris
  open-pull-requests-limit: 3
  target-branch: master
  labels:
    - dependencies
    - <PROJECT_LABEL>
```

PROJECT_ECOSYSTEM: `composer`, `npm`, `docker`
PROJECT_DIRECTORY: Project directory to scan for dependencies
PROJECT_LABEL: `dep:php`, `dep:docker`, `dep:javascript`
