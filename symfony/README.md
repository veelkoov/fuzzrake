# Fuzzrake

See [getfursu.it/info](https://getfursu.it/info)


## Related repositories

* [veelkoov/fuzzrake](https://github.com/veelkoov/fuzzrake) (this repository)
* [veelkoov/fuzzrake-backend](https://github.com/veelkoov/fuzzrake-backend) (backend parts written in a programming language)
* [veelkoov/fuzzrake-data](https://github.com/veelkoov/fuzzrake-data) (periodically updated SQL dumps of the database)


## Requirements

* Docker w/Compose plugin
* ACL-enabled filesystem
* Yarn (to be able to do more than run tests in the `@small` group)


## Quickstart


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
* `yarn install`
* `yarn encore production`
* `./toolbox pum`

To make the `dev` environment and tests in `@large` group work:
* All the above setup
* `./toolbox console doctrine:schema:create`
* Setup reCaptcha (required for some functionalities)


## Known issues ("gotchas")

* Yarn is not dockerized and automated
* Tests in `@medium` group will not work without Yarn
