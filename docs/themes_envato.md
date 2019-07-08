
# Envato / ThemeForest Designers, Sell More Themes!

If you are a web designer who sells via the Envato / ThemeForest marketplace, increase your theme sales by
integrating your themes into Apex!  It's absolutely free of charge, and only requires minimal work to
integrate each theme.  Apex fully integrates with Envta via their API, allowing you to integrate your themes
into Apex, which will then be listed in each user's Maintenance->Theme Manager menu of the admin panel as an
available theme.

When a user decides to use your theme, they will be required to purchase it via ThemeForest.  Upon purchase,
they will obtain their license key with purchase code, which they must enter upon downloading the theme from
the Apex repository, which is verified as a valid purchase via Envato's API.

### Get Apex Account

First, you need to quickly e-mail us at [support@envrin.com](mailto:support@envrin.com), and simply request an
Apex designer account.  Simply provide us with your Envata username, and an Envato API key, which is required
in order to verify purchases from users.


### Integrate Your Themes!

Integrating your themes is very simple.  First, install a copy of Apex on your local computer or server by
following the [Installation Guide](install.md).

Once installed, choose a theme you would like to integrate, and create it within Apex. For example, if
creating the theme named "mycooltheme", at the prompt you would type:

~~~
php apex.php create_theme mycooltheme
php apex.php change_theme public mycooltheme
~~~

Next, open up the file located at /themes/mycooltheme/theme.php, and update the variables as necessary.  Make
sure to define the three Envato based variables, as they are required in order to force users to purchase your
theme from ThemeForest before being allowed to download it.

Next, go ahead and integrate your theme by following the steps listed in the [Integrate Existing
Themes(themes_integrate.md) page of this manual.  Since you've already changed the theme to your via
`change_theme` command, you can always via the current look of your integrated theme at http://localhost/.

Once integrated, simply publish the theme to the main Apex repository by typing the following at terminal:

`php apex.php publish_theme mycooltheme`

That's it!  Your theme is now listed within the Maintenance->Theme Manager menu of all Apex users, with a link
to it on ThemeForest.  If any user decides to use your theme, they will first be required to purchase it from
ThemeForest before being allowed to download and install the integrated theme into Apex.


