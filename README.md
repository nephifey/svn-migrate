# svn-migrate

SVN to Git migration tool that can be used to convert SVN repos to Git repos.

## Prerequisites

Must have [Git](https://git-scm.com/), [Git Svn](https://git-scm.com/) 
(Bundled with Git), and [Svn](https://subversion.apache.org/) installed on your machine.

```
$ git --version
$ git svn --version
$ svn --version --quiet
```

## Getting Started

Install the package into your project using composer via the command below.

```
composer require nephifey/svn-migrate
```

## Documentation

| Command Name                                               | Command         | Aliases          |
|------------------------------------------------------------|-----------------|------------------|
| [Migrate](#migratecoremigrate)                             | `migrate:core`  | `migrate`        |
| [Authors](#migratesvn-create-git-author-filemigrateauthor) | `migrate:svn-create-git-author-file`  | `migrate:author` |
| [Clone](#migrategit-svn-clonemigrateclone)                 | `migrate:git-svn-clone` | `migrate:clone`  |


### migrate:core[migrate]

```
Description:
  Executes all migration core commands

Usage:
  migrate:core [options] [--] <svn-repo-url>
  migrate
  migrate:core https://repositoryhostprovider.com/svn/project
  migrate:core --username=diffusername https://repositoryhostprovider.com/svn/project
  migrate:core --skip-author --author-output-file=path/filename https://repositoryhostprovider.com/svn/project
  migrate:core --author-override-file https://repositoryhostprovider.com/svn/project
  migrate:core --include-metadata https://repositoryhostprovider.com/svn/project
  migrate:core --prefix=/ --trunk=/something --tags=/something2 --branches=/something3 https://repositoryhostprovider.com/svn/project

Arguments:
  svn-repo-url                                          The svn repository url to clone

Options:
  -u, --username=USERNAME                               Username for the svn repository authentication
      --skip-author|--no-skip-author                    Skip the [migrate:author] command
      --author-email=AUTHOR-EMAIL                       Email address used for the map [default: "@company.com"]
      --author-output-file=AUTHOR-OUTPUT-FILE           The name of the output file [default: "authors-file.txt"]
      --author-override-file|--no-author-override-file  If there is a file that exists override it instead of throwing an error
      --trunk=TRUNK                                     The svn repository trunk path [default: "/trunk"]
      --tags=TAGS                                       The svn repository trunk path [default: "/tags"]
      --branches=BRANCHES                               The svn repository trunk path [default: "/branches"]
      --include-metadata|--no-include-metadata          Includes the git-svn-id, can take significantly longer
      --prefix=PREFIX                                   The prefix which is prepended to the names of remotes
  -h, --help                                            Display help for the given command. When no command is given display help for the list command
  -q, --quiet                                           Do not output any message
  -V, --version                                         Display this application version
      --ansi|--no-ansi                                  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                                  Do not ask any interactive question
  -v|vv|vvv, --verbose                                  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### migrate:svn-create-git-author-file[migrate:author]

```
Description:
  Uses svn to create an authors file for git

Usage:
  migrate:svn-create-git-author-file [options] [--] <svn-repo-url>
  migrate:author

Arguments:
  svn-repo-url                            The svn repository url to clone

Options:
  -u, --username=USERNAME                 Username for the svn repository authentication
      --email=EMAIL                       Email address used for the map [default: "@company.com"]
      --output-file=OUTPUT-FILE           The name of the output file [default: "authors-file.txt"]
      --override-file|--no-override-file  If there is a file that exists override it instead of throwing an error
  -h, --help                              Display help for the given command. When no command is given display help for the list command
  -q, --quiet                             Do not output any message
  -V, --version                           Display this application version
      --ansi|--no-ansi                    Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                    Do not ask any interactive question
  -v|vv|vvv, --verbose                    Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### migrate:git-svn-clone[migrate:clone]

```
Description:
  Uses git-svn to clone a svn repository into a git repository

Usage:
  migrate:git-svn-clone [options] [--] <svn-repo-url> [<output-dest>]
  migrate:clone

Arguments:
  svn-repo-url                                  The svn repository url to clone
  output-dest                                   The output destination for the contents of the clone

Options:
  -u, --username=USERNAME                       Username for the svn repository authentication
      --trunk=TRUNK                             The svn repository trunk path [default: "/trunk"]
      --tags=TAGS                               The svn repository trunk path [default: "/tags"]
      --branches=BRANCHES                       The svn repository trunk path [default: "/branches"]
      --author-file=AUTHOR-FILE                 The authors file to use for mapping to Git
      --include-metadata|--no-include-metadata  Includes the git-svn-id, can take significantly longer
      --prefix=PREFIX                           The prefix which is prepended to the names of remotes
  -h, --help                                    Display help for the given command. When no command is given display help for the list command
  -q, --quiet                                   Do not output any message
  -V, --version                                 Display this application version
      --ansi|--no-ansi                          Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                          Do not ask any interactive question
  -v|vv|vvv, --verbose                          Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```