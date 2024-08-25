# Code guidelines

## Acknowledgement

The project started with little-to-none standards and the approach to different aspects was changing over time. When it comes to the coding/naming standards, this document is right, not the existing code.

## Generic

* Stats show that mobile users are the primary audience. Fuzzrake may not be mobile-first, but should be mobile-friendly. This applies less to the administration side.
* Fuzzrake should support NSFW, but must be SFW-first (default to a family-friendly experience).
* In the future, Fuzzrake could be adapted to serve as a list of creators of different types (e.g. artists). This is not a primary objective, but when should be aimed for whenever not too expensive.


## Code expectations

* All new/updated code should be cleaned up using:
    * PHP-CS-Fixer
    * Twig-CS-Fixer
    * ESLint
    * Prettier
* All new/updated Twig functions, filters and variables should use the snake_case.


## Naming, terminology

All the new code should follow the guidelines, while the existing code may be updated when viable.

* _creator_ - old terms: _artisan_, rationale: less exotic, more generic
* _creator ID_ old terms: _maker ID_, rationale: more generic
