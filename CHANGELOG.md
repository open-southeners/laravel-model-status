# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.0] - 2024-03-13

### Added

- Laravel 11 support

## [2.0.4] - 2023-09-18

### Fixed

- Fully qualify columns on statuses query scopes

## [2.0.3] - 2023-03-29

### Fixed

- Models creating event stopping propagation of other events

## [2.0.2] - 2023-02-14

### Added

- Laravel 10 support

## [2.0.1] - 2023-02-03

### Fixed

- StatusEnumCaseName using `$defaultStatus` protected static property

## [2.0.0] - 2023-02-02

### Added

- Status swapping & swapped events (observable) to Eloquent models that uses the `HasStatuses` trait: `swappingStatusName`, `swappedStatusName`. **Do not confuse with saving, saved Eloquent events, these are triggered from setStatus & setStatusWhen functions, so may you always use these**

### Changed

- `setStatus` and `setStatusWhen` functions doesn't return self, instead they return always `true` when not saving, true or false otherwise

### Fixed

- `StatusEnumCaseName` attribute cast now returns `defaultStatus` if defined on the trait, null otherwise

## [1.2.1] - 2023-01-26

### Fixed

- `BackedEnum::tryFrom` throwing deprecation warnings when argument is null

## [1.2.0] - 2023-01-17

### Added

- `ofStatuses` query scope to `HasStatuses` trait

## [1.1.0] - 2023-01-04

### Added

- Status attribute cast for `toArray` / `toJson` Laravel model methods

### Removed

- `open-southeners/laravel-helpers` dependency (not gonna use UnitEnums)

### Fixed

- Inconsistency when getting status enum as attribute

## [1.0.1] - 2022-12-30

### Fixed

- Wrong version to dependency `open-southeners/laravel-helpers`

## [1.0.0] - 2022-12-29

### Added

- Package published on Packagist (composer)
