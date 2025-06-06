# Fuzzrake

See [getfursu.it/info](https://getfursu.it/info)


## Related repositories

* [veelkoov/fuzzrake](https://github.com/veelkoov/fuzzrake) (this repository)
* [veelkoov/fuzzrake-data](https://github.com/veelkoov/fuzzrake-data) (periodically updated SQL dumps of the database)


## Requirements

* Docker w/Compose plugin
* ACL-enabled filesystem
* Yarn


## Quickstart


* Clone
* `./toolbox docker-up`
* `./toolbox composer install`
* `./toolbox yarn install`
* `./toolbox yep`
* `./toolbox console doctrine:schema:create`
* http://localhost:8080/ should now respond
* Tests should now pass: `./toolbox pu` (or `pus` - short, `pum` - medium, `pul` - large)


## Known issues ("gotchas")

* Some extra functionalities rely on Kotlin parts, and there are no docs
