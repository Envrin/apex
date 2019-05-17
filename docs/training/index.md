
# Training Guide

This guide serves to take you through all aspects of developing quality online software operations with Apex, as quickly and efficiently as possible.  Instead of 
doing a standard "Hello World" app, we'll do something a little more real world.  Plus due to time constraints, we're going to go ahead and develop a KYC/AML Verification package, as its a perfect size to cover 
all bases of Apex, and needs to be developed for clients anyway.

We will need our KYC/AML Verification package to support the following:

- Administration defined verification levels, each with different criteria, which can include:
    - E-Mail / Phone Verification
    - Photo ID upload
    - Utility bill upload
    - Registered for X length of time
    - Current balance
    - Total deposits / withdrawals
- With each verification level, administration can also define different features and fees including:
    - Deposit / withdraw fee per payment method
    - Minimum / maximum deposit / withdraw amounts per payment method
- Member area features allowing users to verify account, upload documents, etc.
- Admin panel feature allowing the processing of pending submissions
- Extra tab page when managing user in admin panel, showing their verification documents / status
- E-mail notifications when documents uploaded / processed, etc.
- Integration with NetVerify API, if administrator decides to use them instead of manual photo ID verification
- Must support horizontal scaling via RabbitMQ to ensure it can handle heavy volumes without issue

Below shows the table of contents for this training guide, and natrually, start at 
the [Getting Started](getting_started.md) page.

1. [Getting Started](getting_started.md)
2. [Create Database Tables](create_database.md)







