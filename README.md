# Sameday Courier Shipping Module for Magento 2

A comprehensive Magento 2 extension that integrates Sameday Courier shipping services into your eCommerce platform.

## 📦 Overview

This module provides complete shipping carrier integration for Sameday Courier, allowing you to:

- Offer multiple shipping services (Same-day, Next-day, Locker delivery, Cross-border)
- Create and manage shipping labels (AWBs) directly from Magento admin
- Integrate Easybox lockers with interactive map or dropdown selection
- Support Cash on Delivery (COD) with automatic fee calculation
- Manage pickup points, services, and lockers
- Support for Romania, Bulgaria, and Hungary

## 🚀 Quick Start

### Installation

1. Copy the module to `app/code/SamedayCourier/Shipping/`
2. Run Magento setup commands:
   ```bash
   php bin/magento module:enable SamedayCourier_Shipping
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy
   php bin/magento cache:flush
   ```

### Configuration

1. Go to **Stores → Configuration → Sales → Shipping Methods → Sameday Courier Shipping Module**
2. Enter your Sameday Courier **Username** and **Password**
3. Click **Save Config**
4. Click **Import Data** to import services, pickup points, lockers, and Sameday Nomenclature for Cities and Counties.
5. Configure your preferred settings and enable the module

## 📚 Documentation

For complete user documentation, please see **[document.md](document.md)**

The documentation includes:
- Detailed configuration guide
- Step-by-step instructions for creating AWBs
- Services, pickup points, and lockers management
- Customer experience features
- Troubleshooting tips

## ✨ Key Features

- **Multiple Shipping Services**: 6H, 24H, LockerNextDay, CrossBorder, PUDO
- **AWB Management**: Create, print, track, and manage shipping labels
- **Locker Integration**: Easybox lockers with map or dropdown selection
- **Dynamic Pricing**: Fixed prices, free shipping thresholds, or API-based estimation
- **COD Support**: Automatic Cash on Delivery fee validation (with/without repayment amount)
- **Multi-Country**: Romania, Bulgaria, Hungary
- **City Nomenclature**: Optional Sameday cities integration

## 🔧 Requirements

- **Magento**: 2.3.x and above
- **PHP**: 7.1.0, 7.2.0, 7.3.0, 7.4.0, 8.1.0, 8.2.0, 8.3.0, or 8.4.0
- **Sameday Courier Account**: Active Sameday account and a Sameday API set of credentials

## 📞 Support

If you are facing issues or want to leave feedback, please contact us:

**Email**: plugineasybox@sameday.ro

## 📄 License

This module is provided by Sameday Courier for use with Magento 2.

---

**Version**: 1.9.2
