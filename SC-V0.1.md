
# Salla Coding Challenge



This coding challenge is part of our technical recruitment process for a Senior Backend Engineer role. Please submit your solution within 7 calendar days. If you need clarification, feel free to send an email with your questions. Do not share your results publicly (e.g., no GitHub, no public blog posts).



## Overview



This challenge will test your skills in the following areas of PHP development:

* PHP's OOP implementation (interfaces and design patterns)

* Namespaces, closures/anonymous functions

* JSON data format

* MySQL

* RESTful API integration

* Efficient workload processing

* Unit/feature testing

* Documentation



## Project Description


You are tasked with improving the code, database structure, and importing process of a product management system. A `products.csv` file is provided, containing a list of products to be imported into a database table. The current **products** table structure is as follows:



```sql
CREATE  TABLE `products` (
`id`  int(11) unsigned NOT NULL AUTO_INCREMENT,
`name`  varchar(255) DEFAULT  NULL,
`sku`  varchar(255) DEFAULT  NULL  UNIQUE,
`status`  varchar(255) DEFAULT  NULL,
`variations`  text  DEFAULT  NULL,
`price`  decimal(7,2) DEFAULT  NULL,
`currency`  varchar(20) DEFAULT  NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

```



The import process uses a command in the ImportProducts file. Your task is to address the following challenges in order to improve the product management system to tackle these challenges:



### 1. Refactor the Code!

Restructure the existing code as needed, following best practices for code organization and design patterns. This may include splitting the code into multiple files, validating data, and utilizing framework features.



### 2. Implement Unit and Feature Tests



Write unit tests and/or feature tests to ensure the code's correctness and stability. Ensure adequate test coverage for critical functionality.



### 3. Delete Outdated Products

Modify the import command to soft delete any products no longer in the file (not in the file or flagged as deleted). Add a hint to the deleted record indicating the product was deleted due to synchronization.



### 4: Restructure the Data

Some products have multiple variations based on options like color and size. These variations are stored without quantity or availability information. Modify the database structure to support adding the quantity and availability for each variation.


  ### 5: Integrate with an External Data Source

Extend the service to update product data from a third-party supplier API. The product information endpoint is:

**https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products**

Develop a solution for a daily synchronization process at 12am.


### 6: Improve Performance



Assume that updating any product triggers multiple events:

- Email notification to the warehouse about the new quantity
- Email notifications to customers who requested updates when out-of-stock products become available
- Requests to a third-party application to update product data

>**_You do not need to implement these notifications_**



However, assume each event takes about 2 seconds per product. Simulate this by pausing the script's execution for 2 seconds before processing the next product and  ***a product will not be persisted in products table until all those events are completed successfully*** . Develop a concept to process the products faster, assuming a few hundred thousand rows (you can test on a batch of 200 records).

Consider query optimization, parallelization, and caching.



### Bonus: Provide Documentation



Create clear and comprehensive documentation for your code, detailing its functionality, architecture, and usage. Include any necessary setup instructions and provide examples for interacting with the system.



## **General Hints**

- The final database structure should be based on the latest modifications made during the task steps.

- Employ best coding practices, principles, and design patterns throughout the challenge.

- Ensure the code is easily extendable. For instance, if another third-party API service needs to be integrated in the future, it should be possible to reuse the existing code with minimal updates.

- Be prepared to explain and review your code, justifying your decisions during a potential follow-up discussion.



***All the best***