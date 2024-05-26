<?php
session_start();
$conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
try {
    $sql = 'CREATE DATABASE IF NOT EXISTS ' . DB_NAME;
    $conn->query($sql);
} catch (\Exception $e) {
    die('Database creation failed - ' . $e->getMessage());
}
$conn->select_db(DB_NAME);
try {
    $sql = 'CREATE TABLE IF NOT EXISTS Menu (
        id int(6) unsigned auto_increment primary key,
        name varchar(100) NOT NULL,
        url varchar(255) NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Options (
        id int(6) unsigned auto_increment primary key,
        name varchar(100) NOT NULL,
        value varchar(255),
        description varchar(400)
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS News(
        id int(6) unsigned auto_increment primary key,
        name varchar(255) NOT NULL,
        description varchar(800) NOT NULL,
        image varchar(255) NOT NULL,
        url varchar(255) NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Comments(
        id int(6) unsigned auto_increment primary key,
        fullname varchar(60) NOT NULL,
        email varchar(255) NOT NULL,
        message varchar(500) NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Franchising(
        id int(6) unsigned auto_increment primary key,
        fullname varchar(60) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(13) NOT NULL,
        message varchar(500) NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Categories (
        id int(6) unsigned auto_increment primary key,
        name varchar(100) NOT NULL,
        status enum("active", "deleted") NOT NULL default "active"
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Products (
        id int(6) unsigned auto_increment primary key,
        name varchar(200) NOT NULL,
        price DECIMAL(10, 2) unsigned NOT NULL,
        description varchar(800),
        image varchar(255) NOT NULL,
        reviewers int(7) NOT NULL default 0,
        category_id int(6) unsigned NOT NULL,
        status enum("active", "deleted") NOT NULL default "active",
        foreign key (category_id) references Categories (id)
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Brands (
        id int(6) unsigned auto_increment primary key,
        name varchar(100) NOT NULL,
        image varchar(255) NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Users (
        id int(6) unsigned auto_increment primary key,
        name varchar(30) NOT NULL,
        email varchar(255) NOT NULL,
        password varchar(40) NOT NULL,
        fullname varchar(100) NOT NULL,
        phone varchar(13) NOT NULL,
        role enum("admin", "user") DEFAULT "user" NOT NULL
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Carts (
        id int(6) unsigned auto_increment primary key,
        user_id int(6) unsigned NOT NULL,
        foreign key (user_id) references Users (id)
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Carts_detail (
        id int(6) unsigned auto_increment primary key,
        cart_id int(6) unsigned NOT NULL,
        product_id int(6) unsigned NOT NULL,
        quantity int unsigned NOT NULL,
        foreign key (cart_id) references Carts (id),
        foreign key (product_id) references Products (id)
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Orders (
        id int(6) unsigned auto_increment primary key,
        user_id DECIMAL(10, 2) unsigned NOT NULL,
        total_bill int unsigned DEFAULT 0 NOT NULL,
        foreign key (user_id) references Users (id)
    )';
    $conn->query($sql);
    $sql = 'CREATE TABLE IF NOT EXISTS Orders_detail (
        id int(6) unsigned auto_increment primary key,
        order_id int(6) unsigned NOT NULL,
        product_id int(6) unsigned NOT NULL,
        price DECIMAL(10, 2) unsigned NOT NULL,
        quantity int unsigned NOT NULL,
        foreign key (order_id) references Orders (id),
        foreign key (product_id) references Products (id)
    )';
    $conn->query($sql);
} catch (\Exception $e) {
    die('Error creating tables in database - ' . $e->getMessage());
}
