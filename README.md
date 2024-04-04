# PHP & MariaDB Development Enviroment Tester

## Purpose

This simple app will help setup a working enviroment for your final project. It will also check that PHP is working, and that a connection to the MariaDB database server can be established. 

## Usage

1. Clone this repository.
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. Run the run.cmd script.  
    4.1. On Windows, type **.\run.cmd**.    
    4.2. On macOS or Linux, type: **./run.cmd**.
5. Open [http://localhost:8001](https://localhost:8001) in your browser.

## Details

PHP has been setup as usual. A MariaDB server has also been created. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).
- Client on [http://localhost:8001](https://localhost:8001).

## Story of the API

A user can register an account by default it will be registered as a user, however to be registered as admin this need to be done from the backend, for security reasons. 

## User Story

A user after logging in, can only add the following products: 
KHWM8199911
KHWM8199912
KHMW789991 
KHWP890001 
KHWP890002 
KHSS988881 
KHSS988882 
KHSS988883 
KHHM89762 
KHSB0001 

A product must be entered once and cannot be added again. They have the option to submit a ticket for the product, and the ticket will be sent and visible to the admins.

Users are allowed to register their product which their warranty ends according to what the user entered. 

## Admin Story

An admin can add products and they have fully access to the submitted tickets which then they can reply according to the Ticket ID, which for every ticket submitted there should be a reply which the system won't allow you to add more replies than actual tickets.

Other than that, the admin have the same abilities as a user.



