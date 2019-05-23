
# Apex Training - Create Database Tables

Many of you will probably groan, but at Apex we strongly believe that SQL database schemas should be written in, well...  SQL.  If you do not know 
SQL, it is an extremely easy language to learn the basics, and can dramatically improve the architecture, performance, and stability of your software.  You will notice there is 
a blank file at */etc/marketplace/install.sql*, and this file is executed against the database upon installation of the package.

Open the */etc/marketplace/install.sql* file, and enter the following contents:

~~~

DROP TABLE IF EXISTS market_products;
DROP TABLE IF EXISTS market_products;

CREATE TABLE market_products (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    require_processing TINYINT(1) NOT NULL DEFAULT 1,  
    amount DECIMAL(16,8) NOT NULL DEFAULT 0, 
    recurring_amount DECIMAL(16,8) NOT NULL DEFAULT 0, 
    recurring_interval VARCHAR(10) NOT NULL DEFAULT '', 
    name VARCHAR(100) NOT NULL, 
    description TEXT NOT NULL 
) engine=InnoDB;

CREATE TABLE market_orders (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    require_processing TINYINT(1) NOT NULL DEFAULT 0, 
    userid INT NOT NULL, 
    transaction_id INT NOT NULL, 
    shipping_id VARCHAR(50) NOT NULL DEFAULT '', 
    date_processed DATETIME, 
    note TEXT, 
    FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON DELETE CASCADE
) engine=InnoDB;

~~~

Now simply connect to mySQL via terminal, and copy and paste the above SQL into the mySQL prompt to create the necessary tables.


### Next

Now that we have a small database structure, let's move on to [Member Area - Request Loan](members_request_loan.md).





