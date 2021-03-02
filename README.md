# Custom form element "Linked checkbox"

This TYPO3 extension adds a custom form element "Linked checkbox" to the
TYPO3 form framework. The user is able to define the link target and the
link text.

## Install

Copy the extension folder to `\typo3conf\ext\ `, upload it via extension
manager or add it to your composer.json. Add the static TypoScript
configuration to your TypoScript template.

## Usage

Open the TYPO3 form editor and create a new form/open an existing one. Add
a new element to your form. The modal will list the new custom form element
"Linked checkbox". Provide a label for the checkbox and select a page you
want to link to. Furthermore, you have to set a link text.

### Combination of label and link

The default label consists of the label itself, followed by a link to the
specified page with the given link text.

Example:

* Label: `I accept the `
* Link text: `terms and conditions.`
* Output: `I accept the <a href="/privacy-policy" target="_blank">terms and conditions.</a>`

If want to use the link inside your label, define the link position
in the label with a character substitution.

Example:

* Label: `I have read the %s and accept them.`
* Link text: `terms and conditions`
* Output: `I have read the <a href="/privacy-policy" target="_blank">terms and conditions</a> and accept them.`

#### Link configuration

You can provide additional link configuration which will be used when
generating the link within the label. Note that this can only be defined
in the appropriate `.form.yaml` file but not in the Backend module.

```yaml
type: LinkedCheckbox
identifier: privacy-policy
label: 'I accept the %s.'
properties:
  pageUid: '67'
  linkText: 'terms and conditions'

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
TYPO3 v9 we are aiming to remove this stumbling block to smoothen the element
registration.

## Credits

This exemplary TYPO3 extension was created by Björn Jacob (https://www.tritum.de).
The idea was born at the TYPO3 CertiFUNcation Day 2017. The audience of my talk
kindly asked for such an element. Lightheaded, I said it will not take more
than 30 minutes to create such an extension. Unfortunately, I could not
make it in this time. It took my nearly 1.5 hours to come up with all the
code. The JS part gave me a hard time.

## Thank you

Jochen Weiland - TYPOholic at https://jweiland.net - supported this
challenge in multiple ways. Thanks for being an outstanding part of our
TYPO3 community.