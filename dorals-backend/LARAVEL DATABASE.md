LARAVEL DATABASE





“**Transactions** were implemented in the Appointment Controller to guarantee atomicity whenever a patient or admin creates, updates, or deletes appointments. These operations involve multiple dependent database writes (appointment table, pivot services table, queue number updates, and audit logs). Using DB::transaction() ensures the system never enters a partial or inconsistent state.”



“**Optimization** was performed by adding indexes on frequently queried columns such as scheduled\_date, status, queue\_number, appointment\_id, is\_sent, and patient/service names.”



“These **indexes** support core use cases: listing appointments by date and status, retrieving today’s queue in order, fetching notifications efficiently, and searching patients and services.”



“A dedicated migration (add\_optimization\_indexes) was implemented to apply and manage these performance optimizations.”



A **login\_history** table records every successful authentication event for both administrators and patients. Each row stores the user’s ID, user type (admin or patient), login timestamp, IP address, and user agent string. The AuthController writes to this table inside the patientLogin and adminLogin methods, using the framework’s request metadata to capture client information. This allows the system to audit access patterns, detect suspicious activity, and support security reviews.



The database schema of the D-ORAL system is **normalized** up to Third Normal Form (3NF), which is the appropriate and industry-standard level for operational information systems. All tables satisfy 1NF by storing atomic values and having defined primary keys. The only table with a composite primary key (appointment\_services) satisfies 2NF because all of its attributes depend on the full key. No transitive dependencies exist among non-key attributes, and therefore the schema satisfies 3NF.



Higher normal forms such as BCNF, 4NF, and 5NF address rare anomalies involving multivalued and join dependencies, none of which appear in this system’s domain model. Thus, 3NF is not only sufficient but optimal, balancing data integrity and system performance.





5.1 Restoring the entire database

mysql -u doral\_admin -p dorals\_db < dorals\_backup\_2025\_12\_01.sql





This restores:



schema

tables

data

relationships

indexes

triggers



5.2 Restoring a single table

mysql -u doral\_admin -p dorals\_db < appointments\_backup.sql



5.3 Recovery After Accidentally Dropping a Table



If someone accidentally runs:

DROP TABLE appointments;



You can recover it by:

mysql -u doral\_admin -p dorals\_db < full\_backup.sql



Document this scenario as part of your DRP (Disaster Recovery Plan).















Here's a checklist of the requirements we’ve covered and where they’ve been applied in the database of your system:



1\. Normalization (Up to 3NF):



Applied to: The structure of the database tables.



Details: We’ve ensured that data is organized in a way that minimizes redundancy and dependency (through entities like patients, appointments, admin, etc.).



2\. Trigger:



Applied to: appointments, audit\_log



Details: We created triggers on the appointments table (e.g., after insert/update) to log actions in the audit\_log table for accountability. This was implemented in the migration file 2025\_11\_30\_071318\_create\_appointment\_audit\_triggers.php.



3\. Transaction:



Applied to: AppointmentController (in the store and update methods)



Details: Using transactions ensures that multiple database actions (e.g., creating an appointment and attaching services) are handled atomically. This was added in the AppointmentController to ensure that if something fails, all actions are rolled back.



4\. Login History:



Applied to: login\_history table



Details: We have the login\_history table in the database to record admin and patient login details. A new section in Settings allows the admin to view the login history.



Table:



Columns: id, user\_id, user\_type, login\_time, ip\_address, user\_agent



5\. Audit Logs:



Applied to: audit\_log table



Details: We store all actions performed by users (e.g., admins and patients) in the audit\_log table. The audit log includes actions like appointment creation, updates, and deletions.



6\. Referential Integrity:



Applied to: appointments, audit\_log, patients, services, admin tables



Details: Foreign keys are used to ensure referential integrity between tables. For example:



appointments.patient\_id is a foreign key referencing patients.patient\_id.



appointments.created\_by and appointments.updated\_by reference admin.admin\_id.



audit\_log.user\_id references admin.admin\_id.



7\. Optimization (Index, Vertical/Horizontal Partition, Aggregate, De-normalization):



Applied to:



appointments, login\_history, audit\_log tables



Details:



Indexes have been added on columns that are frequently queried, such as scheduled\_date, status, user\_id, and log\_date.



Vertical Partitioning can be considered for the patients table (splitting sensitive info like passwords and login data).



Horizontal Partitioning could be applied to the appointments table by creating an appointments\_archive table for old records.



De-normalization might be considered for reports to aggregate data for quick access (e.g., creating a summary table).



8\. Encryption:



Applied to: patients.password, admin.password



Details: Passwords are encrypted using Laravel’s Hash class (bcrypt) for security. This ensures that passwords are securely stored in the database and cannot be retrieved in plain text.



9\. Domain Constraints:



Applied to: Multiple tables, including patients, appointments, services, audit\_log



Details:



Data type constraints (e.g., email in patients, status in appointments) ensure that only valid data is entered.



Enum constraints for the status column in the appointments table (e.g., Pending, Confirmed, Completed, Canceled, No-show).



Unique constraints (e.g., email in patients, email in admin).



Foreign Key Constraints for referential integrity, ensuring no invalid references exist.



10\. Limit Database User Privileges:



Applied to: doral\_admin database user.



Details: We created the doral\_admin user with restricted privileges (only SELECT, INSERT, UPDATE, and DELETE permissions on the dorals\_db database). This restricts the user from performing actions like schema modifications, which are reserved for system admins.



CREATE USER 'doral\_admin'@'localhost' IDENTIFIED BY 'password';

GRANT SELECT, INSERT, UPDATE, DELETE ON dorals\_db.\* TO 'doral\_admin'@'localhost';



11\. Recovery and Backup:



Applied to: Entire database (dorals\_db)



Details: We discussed using mysqldump for backup purposes. However, the doral\_admin user might not have the LOCK TABLES privilege, which can cause an issue during backup. You may need to adjust privileges or use an admin-level account for backup purposes.



Example backup command (if privileges are correct):



mysqldump -u admin -p dorals\_db > backup.sql



12\. Optimized Indexing:



Applied to: appointments table



Details: We created an index for the queue\_number column to speed up search operations.



Example:



CREATE INDEX idx\_appointments\_queue\_number ON appointments(queue\_number);





This ensures that search queries involving queue\_number are executed faster.



Checklist Summary:



Normalization: Applied to table structure (patients, appointments, admin, services, etc.)



Trigger: Applied to appointments and audit\_log tables.



Transaction: Applied to AppointmentController (atomic operations for appointment creation and updates).



Login History: Added login\_history table and the ability to display it in the UI.



Audit Logs: Logged actions stored in audit\_log table.



Referential Integrity: Foreign keys across appointments, patients, admin, and audit\_log.



Optimization: Indexes on commonly queried fields like status and scheduled\_date.



Encryption: Passwords encrypted using Laravel’s Hash class.



Domain Constraints: Enum, unique, and foreign key constraints across various tables.



Limit Database User Privileges: Restricted privileges for the doral\_admin user.



Recovery and Backup: Use mysqldump for backups (adjust user privileges as necessary).



Optimized Indexing: Added necessary indexes for optimized performance.

