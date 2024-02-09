# Content Publishing Job

The Content Publishing Job module allows you to display other a block of
contents of your choice on any content type you want. The contents displayed
are related to the current content based on the taxonomy term field you gave.
The second functionality is the possibility to create job that unpublish
automatically a content if its publication date is expired.


## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [Maintainers](#maintainers)


## Requirements

- Drupal 9 or Drupal 10
- Drupal Block module


## Installation

1. Download and place the module in your modules directory (`/modules/custom`).
2. Enable the module through the Drupal administration interface.


## Configuration

1. After enabling the module, configure block settings and permissions.
2. Place the Related contents block in a region using the block layout manager.
3. Then go to the configuration page (`/admin/config/system/publishing-config`)
4. Finally, create jobs to be executed for the content type of your choice.


## Features

- Displays related contents on the content details page.
- Unpublish automatically some contents when they are expired.


## Maintainers

- Mathieu Palouki - [mpalouki](https://www.drupal.org/u/mpalouki)
