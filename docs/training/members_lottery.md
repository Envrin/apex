
# Apex Training - Member Area - Lottery

If you create a new user through the Users->Create New User menu of the administration panel, then login 
to the member's area, you will see our new Financial->Lottery menu that we defined within the package.php file.  Let's develop 
this menu.


### View -- members/financial/lottery

Let's start by creating the view, so in terminal type:

`php apex.php create view members/financial/lottery lottery`

This will create a new .tpl file at */views/tpl/members/financial/lottery.tpl*, and will assign this view to our 
"lottery" package to ensure it gets included when we publish the package.  Open the .tpl file, and enter 
the following contents:

~~~

<h1>Lottery</h1>

<a:box>
    <a:box_header title="Enter Lottery">
        <p>Enter the current lottery for your chance to win by completing the below form.</p>
    </a:box_header>

    <a:form_table><tr>
        <a:ft_custom label="Current Prize" contents="~current_prize~">
        <a:ft_custom label="Ticket Price" contents="~ticket_price~">
        <a:ft_textbox name="num_entries" label="# of Tickets" value="1" width="60px">
        <a:ft_submit value="enter" label="Enter Lottery">
    </a:form_table>

</a:box>

~~~


### View PHP - members/financial/lottery

Every view also has an associated .php file which is executed every time the page is displayed.  Open the 
file at */views/php/members/financial/lottery.php* and enter the following contents.

~~~php
<?php
declare(strict_types = 1);

namespace apex\views;

use apex\app;
use apex\svc\db;
use apex\svc\view;
use apex\svc\debug;
use apex\training\lottery;

/**
 * All code below this line is automatically executed when this template is viewed, 
 * and used to perform any necessary template specific actions.
 */

// Enter lottery
if (app::get_action() == 'enter') { 


// Get current prize
$lottery = app::make(lottery::class);
$current_prize = $lottery->get_current_prize();

// Template variables
view::assign('current_prize', fmoney($current_prize));
view::assign('ticket_price', fmoney((float) app::_config('training:ticket_price')));

~~~







