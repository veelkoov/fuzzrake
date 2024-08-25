# Fuzzrake

See [getfursu.it/info](https://getfursu.it/info)


## Related repositories

* [veelkoov/fuzzrake](https://github.com/veelkoov/fuzzrake) (this repository)
* [veelkoov/fuzzrake-data](https://github.com/veelkoov/fuzzrake-data) (periodically updated SQL dumps of the database)


## Requirements

* Docker w/Compose plugin
* ACL-enabled filesystem
* Yarn (to be able to do more than run tests in the `@small` group)
* TODO: Kotlin stuff


## Quickstart


TODO: Kotlin stuff

To make tests in `@small` group work:

* Clone
* `./toolbox setup`
* `./toolbox docker-up`
* `./toolbox composer install`
* `./toolbox pus`

To make tests in `@medium` group work:
* All the above setup
* `git submodule init`
* `git submodule update`
* `./toolbox yarn install`
* `./toolbox yep`
* `./toolbox pum`

To make the local development environment and tests in `@large` group work:
* All the above setup
* `./toolbox console doctrine:schema:create`
* Setup reCaptcha (required for some functionalities; `grep -R __TODO_PROVIDE_THIS__ ./symfony`)
* `./toolbox pul`
* http://localhost:8080/ should now respond


## Known issues ("gotchas")

* Yarn is not dockerized and automated
* Tests in `@medium` and above groups will not work without Yarn
* Kotlin repo got merged here and docs are not updated
  * What docs am I even talking about?
