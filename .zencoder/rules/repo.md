---
description: Repository Information Overview
alwaysApply: true
---

# CRM System School Information

## Summary
A comprehensive school management system with focus on student records, fee management, and parent/guardian information tracking. The system includes modules for managing student uniforms, flexible fee structures, and payment installments.

## Structure
- **app/**: Core application code including models, controllers, and services
- **database/**: Database migrations and seeders
- **resources/**: Frontend views and assets
- **routes/**: Application routes
- **public/**: Publicly accessible files
- **config/**: Configuration files

## Language & Runtime
**Language**: PHP
**Version**: Laravel Framework
**Database**: MySQL
**Frontend**: Blade templates with Livewire

## Dependencies
**Main Dependencies**:
- Laravel Framework
- Filament Admin Panel
- Livewire
- Blade UI Kit
- Akaunting/Laravel-Money

**Development Dependencies**:
- Laravel Sail
- Laravel Tinker
- Doctrine/DBAL

## Models & Database Structure
**Core Models**:
- Student
- ParentGuardian
- LegalGuardian
- AcademicInfo
- EmergencyContact
- FeesPlan
- StudentFeeRecord
- Installment
- Discount
- Arrear
- OnlinePayment
- Invoice
- Refund
- GuardianAccount
- UniformItem
- StudentUniformItem
- FeeSetting

**Key Relationships**:
- Students have many fee records, uniform items, and academic info
- Fee records have many installments and discounts
- Students belong to parent/guardians

## Fee Management System
**Features**:
- Flexible fee structures based on grade level and program type
- Custom installment amounts with reason tracking
- Uniform item management integrated with fee system
- Discount management (sibling, early payment, staff)
- Late fee calculation with grace period

## Testing
**Framework**: PHPUnit (Laravel's built-in testing)
**Test Location**: /tests directory

## Security & Logging
**Audit System**: Activity logs track all financial transactions
**User Roles**: Role-based access control for different user types
**Notifications**: Financial notifications for payment reminders