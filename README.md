# Family Finance Management System

A production-ready, shared family finance tracking application. It is designed to be used by a single family (using one central account login) with multiple individual members tracking their incomes, expenses, budgets, savings goals, and recurring transactions in a single unified dashboard. 

The system provides dual-entry accounting for transfers between accounts and real-time frontend notifications for budget thresholds.

## Project Structure

This repository is structured as a monorepo containing both the frontend and the backend applications:

*   [`/backend`](./backend) - The Laravel 11 REST API. Handle all data persistence, business logic, authentication, and background scheduling.
*   [`/frontend`](./frontend) - The Vue 3 SPA. A responsive, dynamic user interface built with Vite, Vue Router, Pinia, and Bootstrap.

## Architecture

*   **API Framework**: Laravel 11 
*   **Database**: MySQL 8
*   **Authentication**: Laravel Sanctum (Token-based)
*   **Frontend Framework**: Vue 3 (Composition API)
*   **Build Tool**: Vite
*   **State Management**: Pinia
*   **Styling**: Custom CSS Built on Bootstrap 5
*   **Charts**: Chart.js / Vue-Chartjs

## Core Features
1.  **Member Management**: Add family members (Father, Mother, Child, etc.) and assign transactions to them.
2.  **Account Management**: Track balances across cash, bank accounts, and e-wallets.
3.  **Transaction Tracking**: Record incomes, expenses, and dual-entry transfers between family accounts.
4.  **Budgets**: Set monthly expense limits per category with real-time progress bars and alerts when hitting 80% usage.
5.  **Saving Goals**: Track target amounts and deadlines for specific purchases or funds.
6.  **Recurring Transactions**: Automatically log repeating bills/salaries based on weekly, monthly, or yearly frequencies.
7.  **Analytics Dashboard**: Visual reports, income/expense bar charts, 6-month trend lines, and category distribution pie charts.

## Deployment Roadmap

This application is ready to be dockerized or deployed to a standard LEMP/LAMP stack. Please refer to the specific README files in the `/backend` and `/frontend` directories for detailed local setup instructions.
