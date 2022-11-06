# Fuzzrake

See [getfursu.it/info](https://getfursu.it/info)


## Requirements

* Docker w/Compose plugin
* sudo
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

To make `dev` environment work:
* All the above setup
* `./toolbox console doctrine:schema:create`


## Known issues ("gotchas")

* Yarn is not dockerized and automated
* Tests in `@medium` group will not work without Yarn
* Tests in `@large` group will not work without proper reCaptcha setup
