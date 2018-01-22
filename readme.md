# Webform Protected Downloads
A recreation of Drupal module **Webform Protected Downloads** (original can be found [here](https://www.drupal.org/project/webform_protected_downloads)) for Drupal 8.

### How to use
The module has the following dependencies:   
- webform
- file
- token

Clone the module into modules folder and enable it. Create a new Webform open up its settings (*/admin/structure/webform/manage/{test_webform}/settings*). You should notice a new setting **Protected download files** appears in the settings menu. Check the checkbox **Enable serving protected files after webform submit**, set expire time or leave blank for no expiration and check **One time visit link**. Finally upload a file you wish to serve.

There is a **Webform protected download** token available and can be used in form submission messages or sent via email.
