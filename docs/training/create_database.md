
# Apex Training - Create Database Tables

Many of you will probably groan, but at Apex we strongly believe that SQL database schemas should be written
in, well...  SQL.  If you do not know SQL, it is an extremely easy language to learn the basics, and can
dramatically improve the architecture, performance, and stability of your software.  You will notice there is
a blank file at */etc/lottery/install.sql*, and this file is executed against the database upon
installation of the package.

Open the */etc/lottery/install.sql* file, and enter the following contents:

~~~

DROP TABLE IF EXISTS lottery;
DROP TABLE IF EXISTS lottery_entrants;

CREATE TABLE lottery (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    winner_userid INT NOT NULL DEFAULT 0, 
    status ENUM('pending','rollover','complete') NOT NULL DEFAULT 'pending', 
    total_entrants INT NOT NULL DEFAULT 0, 
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0, 
    lottery_date DATE NOT NULL
) engine=InnoDB;

CREATE TABLE lottery_entrants (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    lottery_id INT NOT NULL, 
    userid INT NOT NULL, 
    num_entries INT NOT NULL DEFAULT 0, 
    amount DECIMAL(12,2) NOT NULL DEFAULT 0, 
    FOREIGN KEY (lottery_id) REFERENCES lottery (id) ON DELETE CASCADE, 
    FOREIGN KEY (userid) REFERENCES users (id) ON DELETE CASCADE
) engine=InnoDB;

~~~

Now simply connect to mySQL via terminal, and copy and paste the above SQL into the mySQL prompt to create the
necessary tables.


### Next

Now that we have a small database structure, let's move on to [Member Area - Lottery](members_lottery.md).


