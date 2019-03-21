
# Apex Training - Create Database Tables

Many of you will probably groan, but at Apex we strongly believe that SQL database schemas should be written in, well...  SQL.  If you do not know 
SQL, it is an extremely easy language to learn the basics, and can dramatically include the architecture, performance, and stability of your software.  You will notice there is 
a blank file at */etc/lending/install.sql*, and this file is executed against the database upon installation of the package.

Open the */etc/lending/install.sql* file, and enter the following contents:

~~~
DROP TABLE IF EXISTS loans_payments;
DROP TABLE IF EXISTS loans;

CREATE TABLE loans (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    userid INT NOT NULL, 
    lender_id INT NOT NULL DEFAULT 0,  
    status VARCHAR(20) NOT NULL DEFAULT 'pending',  
    amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0, 
    fee DECIMAL(10,2) NOT NULL, 
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    title VARCHAR(255) NOT NULL, 
    description TEXT NOT NULL, 
    FOREIGN KEY (userid) REFERENCES users (id) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE loans_payments (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    loan_id INT NOT NULL, 
    amount DECIMAL(10,2) NOT NULL, 
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (loan_id) REFERENCES loans (id) ON DELETE CASCADE
) engine=InnoDB;
~~~

Now simply connect to mySQL via terminal, and copy and paste the above SQL into the mySQL prompt to create the necessary tables.  Now that we have a 
small database structure, let's move on to [Member Area Features](member_area.md).





