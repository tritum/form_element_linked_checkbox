[![CGL](https://github.com/tritum/form_element_linked_checkbox/actions/workflows/cgl.yaml/badge.svg)](https://github.com/tritum/form_element_linked_checkbox/actions/workflows/cgl.yaml)

# Custom form element "Linked checkbox"

This TYPO3 extension adds a custom form element "Linked checkbox" to the
TYPO3 form framework. The user is able to define the link target and the
link text.

## Install

Copy the extension folder to `\typo3conf\ext\ `, upload it via extension
manager or add it to your composer.json. Add the static TypoScript
configuration to your TypoScript template.

## Customization

The extension overrides templates for the following views:
* email to receiver, plain text
* email to receiver, HTML
* email to sender, plain text
* email to sender, HTML
* summary page

This is necessary to render links correctly. By default, the core templates
of the form framework escape any HTML in both email and plain text mails.
Thus, your users would receive mails with broken links.

If you also override those templates, please adopt your files accordingly.

## Usage

Open the TYPO3 form editor and create a new form/open an existing one. Add
a new element to your form. The modal will list the new custom form element
"Linked checkbox". Provide a label for the checkbox including the link text.
Select a page you want to link to.

### Combination of label and link

The default label consists of the label itself, followed by a link to the
specified page with the given link text.

Example:

* Label: `I accept the `
* Link text: `terms and conditions.`
* Output: `I accept the <a href="/terms" target="_blank">terms and conditions.</a>`

If want to use the link inside your label, define the link position
in the label with a character substitution. We highly **recommend** this way.

Example:

* Label: `I have read the %s and accept them.`
* Link text: `terms and conditions`
* Output: `I have read the <a href="/terms" target="_blank">terms and conditions</a> and accept them.`

You can also use more than one link in the checkbox label. For this, just
use the field `additionalLinks` and provide a combination of Page UID and
link text.

Example:

* Label: `I have read the %s and %s and accept them.`
* Link text: `terms and conditions`
* Additional links:
  - `privacy policy`
* Output: `I have read the <a href="/terms" target="_blank">terms and conditions</a> and <a href="/privacy-policy" target="_blank">privacy policy</a> and accept them.`

#### Link configuration

You can provide additional link configuration which will be used when
generating the link within the label. Note that this can only be defined
in the appropriate `.form.yaml` file but not in the form editor and
applies to all generated links.

```yaml
type: LinkedCheckbox
identifier: consent
label: 'I accept the %s and %s.'
properties:
  pageUid: '67'
  linkText: 'terms and conditions'
  additionalLinks:
    83: 'privacy policy'

renderingOptions:
  linkConfiguration:
    # Additional typolink configuration can be inserted here, e.g.:
    no_cache: 1
```

For a full list of available configuration take a look at the
[TypoScript reference](https://docs.typo3.org/m/typo3/reference-typoscript/master/en-us/Functions/Typolink.html).

#### Override default link target

By default, the link target is set to `_blank`. If you want to override it,
just define a custom link configuration `parameter` – either an empty string
or a custom target/additional parameter configuration:

```yaml
renderingOptions:
  linkConfiguration:
    parameter: ''
```

## Possible improvements or changes

Instead of creating a new form element, the existing `Checkbox` form element
could have been extended. In order to provide a more complex example, the
extension creates a new element.

At the time of writing this, you have to provide a small JavaScript snippet
(see `\Resources\Public\JavaScript\Backend\FormEditor\ViewModel.js`). This
snippet is needed to show the custom form element in the form editor. For
future TYPO3 versions we are aiming to remove this stumbling block to smoothen
the element registration.

## Versions

| News     | TYPO3     | PHP       | Notes                                 |
|----------|-----------|-----------|---------------------------------------|
| master   | 9 - 11    | 7.2 - 8.1 |                                       |
| 3.x      | 9 - 11    | 7.2 - 8.1 | Breaking changes. See comments below. |
| 2.x      | 9 - 11    |           |                                       |
| 1.x      | 8 - 9     |           |                                       |

### Breaking changes version 3.x

Version 3.x includes 3 breaking changes:
* [!!!][FEATURE] Move link resolving from field partial to hook [(d128090)](https://github.com/tritum/form_element_linked_checkbox/commit/d12809029fd1415e765db323f840c04fdd10e1f2)
* [!!!][TASK] Drop deprecated hook usage [(90695cf)](https://github.com/tritum/form_element_linked_checkbox/commit/90695cfcdec97a317cea5e3d20fda387700a37cc)
* [!!!][TASK] Harden visibility and usage of FormElementLinkResolverHook [(4ffb57b)](https://github.com/tritum/form_element_linked_checkbox/commit/4ffb57bc81bf45b7aa28d582aea3e3d7a608dd08)

## Credits

This TYPO3 extension was created by [Björn Jacob](https://www.tritum.de) and has
been highly improved by [Elias Häußler](https://haeussler.dev/). The idea was born
at the TYPO3 CertiFUNcation Day 2017. The audience of my talk kindly asked for
such an element. Lightheaded, I said it will not take more than 30 minutes to
create such an extension. Unfortunately, I could not make it in this time.
It took my nearly 1.5 hours to come up with the initial version code.
The JS part gave me a hard time.

## Thank you

Jochen Weiland - TYPOholic at [jweiland.net](https://jweiland.net) - supported this
challenge in multiple ways. Thanks for being an outstanding part of our
TYPO3 community.
