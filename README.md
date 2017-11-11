# Custom form element "Linked Checkbox"

This TYPO3 extension adds a custom form element "Linked checkbox" to the
TYPO3 form framework. The user is able to define the linked page and the
link text.

## Install

Copy the extension folder to `\typo3conf\ext\ ` or upload it via extension
manager. Add the static TypoScript configuration to your TypoScript template.

## Usage

Open the TYPO3 form editor. Add a new element to your form. The modal will
list the new custom form element "Linked Checkbox". Provide a label for the
checkbox and select a page you want to link to. Furthermore, you have to set
a link text.

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

This exemplary TYPO3 extension was created by Björn Jacob/ TRITUM GmbH. The
idea was born at the TYPO3 CertiFUNcation Day 2017. The audience of my talk
kindly asked for such an element. Lightheaded, I said it will not take more
than 30 minutes to create such an extension. Unfortunately, I could not
make it in this time. It took my nearly 1.5 hours to come up with all the
code. The JS part gave me a hard time.

## Thank you

Jochen Weiland - TYPOholic at https://jweiland.net/ - supported this
challenge in multiple ways. Thanks for being an outstanding part of our
TYPO3 community.