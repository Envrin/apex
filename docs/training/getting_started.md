
# Apex Training - Getting Started

First thing is first, visit the [Installation Guide](../install.md) and get an install of Apex up and running.
Once done, install a few base packages we will need with:

`php apex.php install users transaction support devkit`


### Create Package

Next, we need to create our new package which we will call "marketplace".  You can do this in terminal with:

`php apex.php create_package marketplace`

When prompted to select a repository, enter 2 to specify the local repository that was installed with the
devkit package.  This will create our new package including two directories at:

- */src/marketplace* -- Will hold the bulk of PHP code for this package.
- */etc/marketplace* -- The configuration of this package.

The file located at */etc/marketplace/package.php* is the main configuration file for our package.  Open this
file, and you will see a few variables at the top allowing you to define various properties of the package,
but we can leave them for now. The `__construct()` method within this file is the most important, which is
explained in full on the [package.php __construct() Function](../packages_construct.md) page of the
documentation.  This method contains a few arrays, as described below.

Array | Description ------------- |------------- `$this->config` | Key-value pair of all configuration values
used by the package, and their default value upon installation. `$this->hash` | An array with the key being
the name of the hash, and the value being an associative array of key-value pairs of all variables / options
within the hash.  Define any sets of options for select / radio / checkbox lists in this array. `$this->menus`
| An array of arrays, and defined all menus that are included in the package within the administration panel,
member's area, and public web site. `$this->ext_files` | Any external files included in this page, which are
not components. `$this->placeholders` | Allows you to place `<e:placeholder>` tags within member area / public
templates, which then are replaced with the contents defined by the administrator via the CMS->Placeholders
menu of the admin panel.  This is where you define the placeholders that are available for the package.  Used
so if / when a member area template is included in an upgrade, textual modifications the client has made do
not get overwritten. `$this->boxlists` | Used to add entries / define lists of settings.  For example,
Settings->Users and Financial menus of the admin panel are examples of boxlists. `$this->notifications` |
Allows you to have default e-mail notifications created upon package installation, which are managed via the
Settings->Notifiations menu of the administration panel.

Now that we have the basic gist of this method, open up the */etc/marketplace/package.php* file, and change
the `__construct()` method to:

~~~php

public function __construct()
{

// Config variables
$this->config = array(
    'product_thumb_width' => 80,
    'product_thumb_height' => 80
);

// Hash
$this->hash = array();
$this->hash['notification_orders_actions'] = array(
    'pending' => 'Processing Pending',
    'processed' => 'Processed'
);


// Menus -- admin panel
$this->menus = array();
$this->menus[] = array(
    'area' => 'admin',
    'position' => 'before financial',
    'type' => 'parent',
    'icon' => 'fa fa-fw fa-cart',
    'alias' => 'market',
    'name' => 'Marketplace',
    'menus' => array(
        'products' => 'Products',
        'orders' => 'Orders'
    )
);

// Menu -- admin panel settings
$this->menus[] = array(
    'area' => 'admin',
    'parent' => 'settings',
    'position' => 'bottom',
    'alias' => 'marketplace',
    'name' => 'Marketplace'
);

// Menu - member area -- online store
$this->menus[] = array(
    'area' => 'members',
    'parent' => 'financial',
    'position' => 'top',
    'alias' => 'store',
    'name' => 'Online Store'
);


// Notification -- admin -- new order
$this->notifications = array();
$this->notifications[] = array(
    'recipient' => 'admin:1',
    'sender' => 'user',
    'controller' => 'marketplace_orders',
    'content_type' => 'text/plain',
    'subject' => 'New Product Purchase (ID# ~order-id~) - ~order-product_name~',
    'contents' => 'CkhpIHRoZXJlLAoKQSBuZXcgcHJvZHVjdCBvcmRlciBoYXMgYmVlbiBwbGFjZWQsIGFuZCBpcyBjdXJyZW50bHkgYXdhaXRpbmcgcHJvY2Vzc2luZy4gIEJlbG93IHNob3dzIGRldGFpbHMgb24gdGhlIG9yZGVyOgoKLS0tLS0tLS0tLQpPcmRlciBJRDogICAgfm9yZGVyLWlkfgpVc2VybmFtZTogICAgIH51c2VybmFtZX4KRnVsbCBOYW1lOiAgICB+ZnVsbF9uYW1lfgpFLU1haWw6ICAgICAgfmVtYWlsfgoKUHJvZHVjdDogICAgfm9yZGVyLXByb2R1Y3RfbmFtZX4KQW1vdW50OiAgICAgfm9yZGVyLWFtb3VudH4KRGF0ZSBBZGRlZDogICAgIH5vcmRlci1kYXRlX2FkZGVkfgotLS0tLS0tLS0tCgpUaGFuayB5b3UsCgp+c2l0ZV9uYW1lfgp+aW5zdGFsbF91cmx+CgoKCg==',
    'cond_action' => 'pending'
);

// Notification -- user -- order processed
$this->notifications[] = array(
    'recipient' => 'user',
    'sender' => 'admin:1',
    'controller' => 'marketplace_orders',
    'content_type' => 'text/plain',
    'subject' => 'Order Processed (ID# ~order-id~) - ~order-product_name~',
    'contents' => 'CkhpIH5mdWxsX25hbWV+LAoKQSByZWNlbnQgcHJvZHVjdCBvcmRlciB5b3UgcGxhY2VkIHdpdGggdXMgYXQgfnNpdGVfbmFtZX4gaGFzIGJlZW4gc3VjY2Vzc2Z1bGx5IHByb2Nlc3NlZCB3aXRoIHRoZSBzaGlwcGluZyBJRDogfm9yZGVyLXNoaXBwaW5nX2lkfi4gIEJlbG93IHNob3dzIGRldGFpbHMgb24gdGhlIG9yZGVyLgoKLS0tLS0tLS0tLQpPcmRlciBJRDogICAgfm9yZGVyLWlkfgpQcm9kdWN0OiAgICAgfm9yZGVyLXByb2R1Y3RfbmFtZX4KQW1vdW50OiAgICAgIH5vcmRlci1hbW91bnR+CgpEYXRlIEFkZGVkOiAgICB+b3JkZXItZGF0ZV9hZGRlZH4KU2hpcHBpbmcgSUQ6ICAgfm9yZGVyLXNoaXBwaW5nX2lkfgpOb3RlOiAgICAgIH5vcmRlci1ub3RlfgotLS0tLS0tLS0tCgoKVGhhbmsgeW91LAoKfnNpdGVfbmFtZX4Kfmluc3RhbGxfdXJsfgoKCg==',
    'cond_action' => 'processed'
);



}

~~~


### Scan Package

We will explain the code in detail just below, but every time you modify a package.php file, you must scan the
package to update the database as necessary.  In terminal, simply type:

`php apex.php scan marketplace`

Once done, if you login to either the administration panel or member's area, you will see the new menus we
added.


### __construct() Code Explained

The rest of this page explains the above code in detail, but for full information on the `__construct()`
function within the package.php file, please visit [package.php __construct()
Function](../packages_construct.md).


##### `$this->config`

We added two configuration variables for the size of the product thumnail images.  As with all configuration
variables, these can be accessed anywhere within the software with:

~~~php
$width = registry::config('marketplace:product_thumb_width');
$height = registry::config('marketplace:product_thumb_height');
~~~with:

You may also update the value of the configuration variables anywhere within the software by using the
`registry::update_config_var()` method such as:

~~~php
$width = 120;
registry::update_config_var('marketplace:product_thumb_width', $width);
~~~


##### `$this->hash`

We added one simple hash, which was the action being performed for when sending e-mail notifications for this
package.  Hashes are generally used to easily populate select lists.  Don't worry if this does not make sense
right now, but for quick reference, you can add these into HTML forms with one of two ways:

**Form PHP Class**

~~~php
$form_fields['action'] = array('field' => 'select', 'data_source' => 'hash:marketplace:notification_orders_actions', 'required' => 1);
~~~

**TPL Code**

~~~
<e:ft_select name="action" data_source="hash:marketplace:notification_orders_actions" required="1">
~~~


##### `$this->menus`

As stated in the documentation, this is an array of associative arrays that defines the various menus to add
for this package, and should be quite straight forward.  In the above code, we are added one sub-menu under
the Settings menu within the administration panel, plus adding one parent menu with two sub-menus in the
administration panel, plus one sub-menus into the member's area for the online store.

##### ~$this->notifications`

Simply adds two default e-mail notifications, making setup easier and quicker for any end-users who install
the package.  Please note, this e-mail notifications are onyl created when the package is initially installed,
and not when the package is scanned during development.

### Next

Now that our new package is created with some base configuration, let's move to the next step, [Create
Database Tables](create_database.md).



