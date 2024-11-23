- documentation:
    - https://docs.codecov.com/docs/quick-start

## Codecov

Project file `codecov.yml`.
Template to add a new project:

```yml
coverage:
  status:
    project:
      
      ...
      
      laravel-infrastructure:
        target: <PROJECT_COVERAGE_TARGET>%
        flags:
          - <PROJECT_NAME_AS_FLAG>

flags:
  
  ...

  <PROJECT_NAME_AS_FLAG>:
    carryforward: true
    paths:
      - <PROJECT_DIRECTORY>/

```

PROJECT_COVERAGE_TARGET: numeric value between 0 and 100
PROJECT_NAME: Project name to display in the Codecov dashboard
PROJECT_DIRECTORY: Project directory to scan for coverage

## Comment section

- https://docs.codecov.com/docs/pull-request-comments

This section allows to configure the comment behavior on the pull request.

```yml
comment:
  layout: "flags, files"
  behavior: default
  require_changes: false # if true: only post the comment if coverage changes
  require_base: no # [yes :: must have a base report to post]
  require_head: no # [yes :: must have a head report to post]
```
