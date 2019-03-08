
# Getting Started

This document assumes Apex has already been installed on your server, and you have access to the administration panel.  If not, please either contact technical support to have 
Apex installed, or view the [Installation](../installation.md) page of this manual.  Once Apex has been installed, visit the administration panel where 
you will be prompted to create your first administrator.

Once inside your administration panel, there are a few things you must do in order for Apex to work properly, mainly creating 
various free API keys.  Follow the below steps.

### DigitalOcean

Although not required, it is highly recommended you host with DigitalOcean, as they provide excellent and cost effective hosting infrastructure, 
plus Apex fully integrates with their API allowing us to easily monitor and maintain your cluster with quality, allowing you to concentrate on your business without having to worry about any technical aspects.

If you haven't already, please sign up for an account with [DigitalOcean](https://digitalocean.com/).  Once logged into your DigitalOcean 
account, ensure to add one credit card to the account.  Then via the [Generate API Token](https://cloud.digitalocean.com/account/api/tokens/new) page, generate one API key.  Provide 
technical support with your API key, as this will allow us to monitor your cluster, receive alerts via e-mail and SMS when either resource usage is high or one of the droplets become unresponsive.  It will also allow us to easily deploy new droplets 
into your cluster if / when resource usage gets heavy, providing you with peace of mind that your operation will always be able to handle any volume size that.  You will receive e-mail notifications 
every time we deply a new droplet into your cluster, informing you resource usage was high and an additional droplet was required to handle the volume.


#### Other API Keys

There are various other API keys required for Apex to operate correctly, as listed below.  All API keys can be entered via the Settings->General menu of the 
administration panel, in the first tab named General.

1. **reCaptcha** -- Apex utilizes Google's popular noCaptcha reCaptch, which is that checkbox users must click on to confirm they are human.  Generate 
an API key-pair at [Google reCaptcha API](https://www.google.com/recaptcha/admin) and enter the API key and secret into the Settings menu.

2. **Nexmo** -- Only required if you wish to send out SMS messages from the software allowing users to verify their phone number, and authenticate via SMS when logging in, processing a withdrawal, etc.  You can 
signup for an account at the [Nextmo site](https://nexmo.com/), and they do provide a free trial credit with all new accounts.  However, after the tirla credit is used, Nexmo is a paid service, but is best in the business for sending out SMS messages from software.

3. **OpenExchange** -- Only required if you're providing support for multiple fiat currencies, and is used to obtain updated exchange rates.  You can sign up for 
a free account at [OpenExchange](https://openexchange.org/) that allows for 1000 requests per-month, which is enough to obtain the current exchange rates every hour.  You can change this to obtain the current exchange rates more frequently, 
but it will require you signing up for a paid account with OpenExchange.


Obtain the necessary API keys as described above, and enter them into the fields provided within the first tab of the Settings->General menu of the administration panel.


### Overall Settings

Through the Settings->General menu, also ensure you complete the Site Info tab, as that is the information that will be displayed on your public site.  Then simply go through the 
sub-menus within the Settings menu, such as Users and Financial, and check out the various settings available to you.  Most settings should be 
quite self explanatory and straight forward.





