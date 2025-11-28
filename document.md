# Sameday Courier Shipping Module for Magento 2 - User Guide

## Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Configuration Guide](#configuration-guide)
4. [Managing Shipping Services](#managing-shipping-services)
5. [Creating and Managing AWBs](#creating-and-managing-awbs)
6. [Pickup Points Management](#pickup-points-management)
7. [Lockers (Easybox) Setup](#lockers-easybox-setup)
8. [Cities and Counties](#cities-and-counties)
9. [Cash on Delivery (COD)](#cash-on-delivery-cod)
10. [Customer Experience](#customer-experience)
11. [Support & Contact](#support--contact)

---

## Overview

The **Sameday Courier Shipping Module** is a complete shipping solution for your Magento 2 store that integrates with Sameday Courier services. This module allows you to offer fast and reliable shipping options to your customers in Romania, Bulgaria, and Hungary.

### What This Module Does

- **Offers Multiple Shipping Options**: Same-day delivery (6H), next-day delivery (24H), locker delivery, and cross-border services
- **Manages Shipping Labels**: Create, print, and track shipping labels (AWBs) directly from your Magento admin
- **Handles Lockers**: Integrates Easybox lockers so customers can pick up packages at convenient locations
- **Supports Cash on Delivery**: Automatically calculates and adds COD fees to orders
- **Simplifies City Selection**: Optionally uses Sameday's city list for more accurate shipping

### Supported Countries

- 🇷🇴 Romania (RO)
- 🇧🇬 Bulgaria (BG)
- 🇭🇺 Hungary (HU)

---

## Getting Started

### Initial Setup

After the module is installed, you need to configure it with your Sameday Courier account credentials.

1. Go to **Stores → Configuration → Sales → Shipping Methods → Sameday Courier Shipping Module**
2. Enter your **Username** and **Password** from your Sameday Courier account
3. Click **Save Config**
4. The module will automatically detect your account environment (Production or Testing)

### Importing Initial Data

Before you can start using the module, you need to import the necessary data:

1. In the configuration page, click the **Import Data** button
2. This will import:
   - Available shipping services
   - Pickup points
   - Lockers
   - Cities and counties

**Note**: You can also import these individually using the dedicated buttons for each data type.

---

## Configuration Guide

### Basic Configuration

Navigate to: **Stores → Configuration → Sales → Shipping Methods → Sameday Courier Shipping Module**

#### Essential Settings

1. **Enabled**
   - Turn the shipping method on or off
   - Must be enabled for customers to see Sameday Courier as a shipping option

2. **Title**
   - The name customers will see (e.g., "Sameday Courier" or "Fast Delivery")
   - You can customize this per store view

3. **Username & Password**
   - Your Sameday Courier API credentials
   - Required to connect to Sameday's system
   - Password is securely encrypted

4. **Shipping Cost**
   - Default shipping price
   - Can be customized per service (see Services Management)

#### Display Settings

5. **Show Method if Not Applicable**
   - Choose what customers see when Sameday Courier is not available for their location
   - You can show an error message or hide the method completely

6. **Show Sameday easybox method**
   - **Show Sameday easybox map**: Customers see an interactive map to select lockers
   - **Show Sameday easybox dropdown**: Customers see a simple dropdown list of lockers
   - Choose based on your preference and customer experience goals

7. **AWB Label Format**
   - Choose the PDF format for shipping labels (A4 or A6)
   - A4 is standard size, A6 is smaller (label printer size)

#### Cash on Delivery Settings

8. **Repayment tax label**
   - Custom text shown to customers for the COD fee
   - Example: "Cash on Delivery Fee" or "COD Charge"

9. **Repayment tax**
   - The fee amount charged for Cash on Delivery orders
   - Automatically added when customer selects COD payment

#### Advanced Options

10. **Locker max items**
    - Maximum number of items allowed in an order for locker services
    - Default: 5 items
    - Orders with more items won't show locker options

11. **Displayed Error Message**
    - Custom message shown when shipping method is unavailable
    - Example: "Sameday Courier is not available for your location"

12. **Use Sameday Cities nomenclature**
    - Enable this to use Sameday's official city list in checkout
    - Ensures cities match exactly with Sameday's system
    - Improves shipping accuracy

13. **Sort Order**
    - Position of Sameday Courier in the shipping methods list
    - Lower numbers appear first (default: 15)

---

## Managing Shipping Services

### Viewing Services

Go to: **Sameday Courier → Services**

Here you can see all available shipping services from Sameday Courier:

- **6H**: Same-day delivery within 6 hours
- **24**: Next-day delivery within 24 hours
- **LN (LockerNextDay)**: Next-day delivery to Easybox lockers
- **XB**: Cross-border next-day delivery
- **XL**: Cross-border locker delivery
- **PP (PUDO)**: Pick Up Drop Off service

### Configuring a Service

Click **Edit** on any service to configure:

1. **Name**: Custom display name for customers
   - Defaults to Sameday's name, but you can customize it
   - Example: Change "LockerNextDay" to "Pick up at Easybox"

2. **Price**: Fixed shipping cost for this service
   - Set to 0 if you want to use estimated costs from API

3. **Free Shipping Threshold**:
   - **Is Price Free**: Enable free shipping above a certain order value
   - **Price Free**: Minimum order amount for free shipping
   - Example: Free shipping for orders over 200 RON

4. **Use Estimated Cost**: 
   - Enable to calculate shipping cost automatically based on package weight and destination
   - More accurate pricing but requires API call

5. **Status**: Enable or disable the service
   - Disabled services won't appear to customers

6. **Service Optional Taxes**: Additional fees (like PDO insurance)
   - Usually configured automatically

### Refreshing Services

Click **Refresh** to update services from Sameday's system:
- New services will be added
- Existing services will be updated
- Removed services will be deleted

**Tip**: Refresh services periodically to ensure you have the latest options available.

---

## Creating and Managing AWBs

### Creating an AWB

When a customer places an order with Sameday Courier shipping:

1. Go to **Sales → Orders** and open the order
2. Scroll to the **Sameday Section** (appears for Sameday Courier orders)
3. Review and complete the AWB creation form:

   **Important**: The form is automatically pre-filled and pre-validated with information from the order to save you time. However, you can modify any field before submitting the form.

   **Pre-filled Fields**:
   - **Service**: Automatically selected based on the order's shipping method (e.g., if customer chose "LockerNextDay", that service is pre-selected)
   - **Pickup Point**: Pre-selected with your default pickup point
   - **Package Weight**: Pre-filled with the order's total weight (minimum 1.0 kg if order weight is 0)
   - **Repayment**: Pre-filled with the order's grand total if Cash on Delivery payment method was used, otherwise set to 0
   - **Client Reference**: Pre-filled with the order ID
   - **Locker Details**: If a locker service was selected, the locker information is automatically displayed

   **Pre-validated Fields**:
   - **Service**: Required - must be selected
   - **Pickup Point**: Required - must be selected
   - **Customer Email**: Validated from order (required for AWB creation)
   - **Customer Phone**: Validated from shipping address (required for AWB creation)

   **Fields You Can Edit**:
   - **Service**: Change to a different service if needed
   - **Pickup Point**: Select a different pickup point
   - **Packages**: Number of packages in the shipment (default: 1)
   - **Package Details** (for each package):
     - **Weight**: Adjust package weight if different from order total
     - **Length, Width, Height**: Add package dimensions in cm (optional but recommended for accurate pricing)
   - **Insured Value**: Set value of goods for insurance (default: 0)
   - **Repayment**: Adjust Cash on Delivery amount if needed
   - **Client Reference**: Modify your internal reference (defaults to order ID)
   - **Observation**: Add special instructions or notes for the courier
   - **Locker Selection**: Change locker location if using a locker service (click "Change location" button)

4. Review all fields and make any necessary adjustments
5. Click **Create AWB**
6. The AWB number will be generated and saved
7. A shipment will be automatically created with tracking information

**Note**: If required fields (Service, Pickup Point, Email, or Phone) are missing, you'll see an error message and the AWB will not be created until all required information is provided.

### Viewing AWB History

Go to: **Sameday Courier → AWB History**

View all created AWBs with:
- Order information
- AWB numbers
- Costs
- Number of parcels

### Printing AWB Labels

1. In the order view, find the AWB section
2. Click **Download PDF** button
3. The shipping label will download in the format you configured (A4 or A6)
4. Print and attach to your package

### Adding Additional Parcels

If you need to add more packages to an existing AWB:

1. In the order's AWB section, click **Add Parcel**
2. Enter the new package details (weight and dimensions)
3. The parcel will be added to the existing AWB
4. Each parcel gets its own AWB number

### Removing an AWB

To cancel and delete an AWB:

1. In the order view, find the AWB section
2. Click **Remove AWB**
3. Confirm the deletion
4. The AWB will be removed from Sameday's system and your database

**Warning**: Only remove AWBs if the package hasn't been picked up yet.

---

## Pickup Points Management

### Viewing Pickup Points

Go to: **Sameday Courier → Pickup Points**

See all available pickup points where you can drop off packages:
- Location details (city, county, address)
- Contact information
- Default pickup point indicator

### Setting Default Pickup Point

The default pickup point is automatically used for:
- Shipping cost estimation
- AWB creation (pre-selected)

The default pickup-point option value came from SamedayCourier API. You can change it only in eAWB platform.

### Refreshing Pickup Points

Click **Refresh** to update the pickup points list:
- New locations will be added
- Existing locations will be updated
- Closed locations will be removed

**Tip**: Refresh pickup points monthly to ensure you have current locations.

---

## Lockers (Easybox) Setup

### Understanding Lockers

Easybox lockers allow customers to pick up packages at convenient self-service locations. The module supports three locker services:

- **LockerNextDay (LN)**: Next-day delivery to lockers
- **CrossBorder Locker (XL)**: Cross-border locker delivery
- **PUDO (PP)**: Pick Up Drop Off service

### Display Options

Choose how customers select lockers:

**Option 1: Interactive Map** (Recommended)
- Customers see Sameday's official locker map
- Fast enough due to our pagination system that queries a small amount of data based on the client's initial location and scales up based on user scrolling and filtering
- Click on map to select locker
- More visual and user-friendly

**Option 2: Dropdown List**
- Simple dropdown menu grouped by city
- Faster loading because it queries info from your local server (See below for import of lockers)
- Works better on mobile devices
- Less visual but more straightforward
- Cons: Lack of support for filtering

Configure this in: **Stores → Configuration → Show Sameday easybox method**

### Managing Lockers

Go to: **Sameday Courier → Lockers**

View all available lockers with:
- Location information
- Address details
- Coordinates

Click **Refresh** to update the locker list from Sameday's system. Please be aware that you don't need to import 
locally our list of easybox if you want to use only the interactive map. But also this feature can be a fallback solution from situation of facing trouble with interactive map.

### Locker Restrictions

- Maximum items per order: Configurable (default: 5 items)
- Orders exceeding this limit won't show locker options
- Configure in: **Stores → Configuration → Locker max items**

### Changing Locker Selection

If a customer needs to change their locker selection after ordering:

1. Open the order in admin
2. In the Sameday section, click **Change Locker**
3. Select a new locker
4. The shipping address will be updated automatically

---

## Cities and Counties

### Using Sameday Cities

Enable **Use Sameday Cities nomenclature** in configuration to:
- Show only cities recognized by Sameday in checkout
- Ensure accurate shipping address matching
- Reduce shipping errors

When enabled:
- Customers see a dropdown of Sameday cities
- Cities are filtered by selected county/region
- Improves shipping accuracy

### Importing Cities and Counties

1. Go to **Stores → Configuration → Sameday Courier Shipping Module**
2. Click **Import Data** button
3. This imports:
   - Counties/regions for Romania, Bulgaria, and Hungary
   - Cities mapped to Sameday's system

**Note**: Cities are cached for better performance. If you want to refresh the cache you have to do it manually by re-import the data.

---

## Cash on Delivery (COD)

### How It Works

When customers select Cash on Delivery as payment method:
- A repayment fee is automatically added to the order
- The fee amount is configurable
- The fee label can be customized

### Configuration

1. Go to **Stores → Configuration → Sameday Courier Shipping Module**
2. Set **Repayment tax**: The fee amount (e.g., 5.00)
3. Set **Repayment tax label**: What customers see (e.g., "COD Fee")

### Supported Payment Methods

COD fee is automatically added for:
- Cash on Delivery payment method
- Check/Money Order payment method

The fee appears in:
- Checkout totals
- Order totals
- Invoice totals

---

## Customer Experience

### Checkout Process

1. **Shipping Method Selection**:
   - Customer sees available Sameday Courier services
   - Prices are displayed (fixed or estimated)
   - Services are filtered based on delivery address

2. **Locker Selection** (if locker service chosen):
   - Customer selects locker using map or dropdown
   - Selection is saved automatically
   - Shipping address updates to locker location

3. **City Selection** (if Sameday cities enabled):
   - Customer sees Sameday city dropdown
   - Cities filtered by selected county
   - Ensures accurate address matching

4. **Order Placement**:
   - Order is created with shipping details
   - Locker information saved (if applicable)
   - COD fee added (if applicable)

### What Customers See

- **Shipping Options**: Available services with prices
- **Locker Map/Dropdown**: Easy locker selection
- **Tracking**: AWB number for package tracking
- **COD Fee**: Transparent fee display in checkout

### Order Tracking

Customers can track their orders using:
- The AWB number provided
- Magento's standard tracking system
- Sameday's tracking website

---

## Support & Contact

### Need Help?

If you encounter any issues or have questions about the Sameday Courier Shipping Module, please contact us:

**Email**: plugineasybox@sameday.ro

### Module Information

- **Module Version**: 1.9.2
- **Supported Magento Versions**: 2.3.x and above
- **Supported Countries**: Romania, Bulgaria, Hungary

### Additional Resources

- For Sameday Courier API documentation, visit Sameday's official documentation
- For Magento 2 help, refer to Magento's official documentation

---

## Quick Reference

### Service Codes

| Code | Service | Description |
|------|---------|-------------|
| 6H | Same-day 6H | Same-day delivery within 6 hours |
| 24 | Next-day 24H | Next-day delivery within 24 hours |
| LN | LockerNextDay | Next-day delivery to locker |
| XB | CrossBorder 24H | Cross-border next-day delivery |
| XL | CrossBorder Locker | Cross-border locker delivery |
| PP | PUDO | Pick Up Drop Off service |

### Default Settings

- **Locker Max Items**: 5
- **Default Shipping Cost**: 10
- **Sort Order**: 15

### Important Locations in Admin

- **Configuration**: Stores → Configuration → Sales → Shipping Methods → Sameday Courier
- **Services**: Sameday Courier → Services
- **Pickup Points**: Sameday Courier → Pickup Points
- **Lockers**: Sameday Courier → Lockers
- **AWB History**: Sameday Courier → AWB History
- **Orders**: Sales → Orders

---

*This user guide covers all features of the Sameday Courier Shipping Module. For technical implementation details, please refer to the module's codebase or contact support.*
