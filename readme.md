# ACF Block Style location

```
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!!! WARNING! This plugin is probably unstable since it fiddles its way around to find React instances in the page.!!!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
```

Now that you've been warned. Hey, welcome! For those of you who use [block styles](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/) and [ACF Blocks](https://www.advancedcustomfields.com/resources/acf_register_block_type/), you probably wonder why it's not possible to register a group field for a specific block style.

The simple reason is probably because it's unreliable right now in the current Gutenberg APIs (2021-12-20), but it might change soon. In the meantime, I've hacked around this solution that 1) caches the block styles as a transient; 2) finds the ACF form React instance in the dom; 3) forces the refresh of the ACF form when we click a block style.

This is far from perfect, but if it can be of any use to someone, good!


## Requirements

- [Composer](https://getcomposer.org/download/)

## Installation

Install via Composer:

```bash
composer require davidwebca/acf-block-style-location
```

Or simply download zip of this repo and place in your plugins directory!

## Bug Reports and contributions

All issues can be reported right here on github and I'll take a look at it. I don't intend on maintaining this a lot this the APIs will vastly change in the next few releases of Gutenberg, but please don't hesitate to ask around and create issues on Github. Make sure to give as many details as possible since I'm working full-time and will only look at them once in a while. Feel free to add the code yourself with a pull request.

## License

This code is provided under the [MIT License](https://github.com/davidwebca/acf-block-style-location/blob/master/LICENSE.md).