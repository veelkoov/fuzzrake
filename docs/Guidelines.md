# Guidelines

## Acknowledgement

The project started with little-to-none standards and the approach to different aspects was changing over time. When it comes to any standards, this document is right, not the existing code.

Being far from the ideal does not mean taking a step towards it is not worth the effort.


## Values (ideals)

* Family friendly
  * A user should not even _see_ any _options_ related to 18+ stuff if they first don't claim they are adults first
  * An adult should be given a default, SFW experience
  * Questionable and/or suggestive stuff is not SFW
* Free
  * Big creators would not care about being featured if they had to pay; we want the big creators to join
  * Fuzzrake should be a helpful tool for the new and small creators, not an unavoidable toll collection gateway
  * No promoted search results (see: _Helpfulness_). Reasonably sized banners would be OK
* Lasting
  * Keep bus factor at the right level
  * Open source, open data
* Helpfulness
  * Easy to filter by rich data sets with accurate and rich results
* Transparency
* Fairness


## Generic things

* Stats show that mobile users are the primary audience. Fuzzrake may not be mobile-first, but should be mobile-friendly. This applies less to the administration side.
* In the future, Fuzzrake could be adapted to serve as a list of creators of different types (e.g. artists). This is not a primary objective, but when should be aimed for whenever not too expensive.


## Code guidelines

* All new/updated code should be cleaned up using:
    * PHP-CS-Fixer
    * Twig-CS-Fixer
    * ESLint
    * Prettier
* No change in the code should cause any existing tests to fail.


## Naming, terminology

All the new code should follow the guidelines, while the existing code may be updated when viable.

* _creator_ - other terms: _artisan_, _maker_, rationale: less exotic, more generic
* _creator ID_ old terms: _maker ID_, rationale: more generic

Frontend should still use the terms "maker" and "maker ID".
