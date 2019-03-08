
# DigitalOcean API

Apex comes with a package that fully integrates with DigitalOcean's API, allowing for easy and efficient horizontal scaling and server monitoring of your clients.  Your clients 
simply need to sign up for a DigitalOcean account, and provide you with their API key.  You can then instantly deply new clusters, monitor all aspects, receive e-mail / SMS alerts when resource usage is high or a server goes offline, 
instantly add new droplets into a cluster, and more.

**NOTE:** As of this writing, deployment scripts and images are not yet in place, but will be shortly.  At the moment, this package simply creates the droplets with Ubuntu 18.04 as the OS, but there is no configuration or deployment yet in place.


## Installation

Installation is very simple.  Assuming Apex is already installed, simply change to the installation directory and within terminal type:

`php apex.php install digitalocean`


## Overview

The structure of this package is fairly basic, and should be quite straight forward.  First, you need to obtain the necessary API keys from DigitalOcean from your or your 
client's accounts.  Then visit the *DigitalOcean->Manage API Keys* menu of the administration panel, and setup the necessary clients.

Once done, visit the *DigitalOcean->Create New Cluster* menu, and setup the necessary clusters.  If you already have droplets setup for the account, simply select the radio button to 
not create any droplets.  The software will automatically scan all droplets on all API keys every 12 hours, and update the database of droplets as necessary within the package to ensure all manually created droplets also get picked up.

From there, you can easily manage all server resource usage warnings, manage existing clusters and droplets, and instantly add new droplets with the click 
of a mouse using the menus available.  Ensure to visit the *digitalOcean->Settings* menu as well, and enter the necessary e-mail and phone numbers for all technical contacts, and 
the software will automatically notify you when either resource usage is getting high on any single droplet, or when a droplet goes offline.

  
