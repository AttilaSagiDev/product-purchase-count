# **Magento 2 Product Purchase Count Extension** #


## Description ##

The extension adds functionality to show how many unique customers bought the specific product in a customizable interval, in the last three or seven days. The module can be configured to set custom notification message. It's also available to select two different positions on product view page.

The administrator can customize the order states and maximum orders count for calculation.

## Features ##

- Module enable / disable
- Interval selection, three or seven days
- Custom notification text
- Display position on product page
- Completed only or all order states
- Maximum order number for calculation
- Multistore support
- Supported languages: English

It is a separate module that does not change the default Magento files.

Support:
- Magento Community Edition 2.4.x
- Adobe Commerce 2.4.x

## Installation ##

** Important! Always install and test the extension in your development environment, and not on your live or production server. **

1. Backup Your Data Backup your store database and whole Magento 2 directory.

2. Enable extension Please use the following commands in your Magento 2 console:

   ```
    bin/magento module:enable Space_ProductPurchaseCount

    bin/magento setup:upgrade
    ```

## Configuration ##

Login to Magento backend (admin panel). You can find the module configuration here: Stores / Configuration, in the left menu Space Extensions / Product Purchase Count.

Settings:

### Configuration ###

Enable Extension: Here you can enable the extension.

### Display ###

Interval: Select 3 days or 7 days interval.

Notification Text: The notification text which will be displayed. Please note that the "%c" will be replaced with the count value, so you have to add it in the proper place within the text. You can use only strong HTML tag.

Display Position: Please select where to display the notification on the product view page.

### Orders Settings ###

State: Please select order state for calculation.

Maximum Orders: Please select maximum orders value for calculation. This must be between 10 and 300.

## Change Log ##

Version 1.0.0 - Sep 21, 2024
- Compatibility with Magento Community Edition 2.4.x
- Compatibility with Adobe Commerce 2.4.x

## Support ##

If you have any questions about the extension, please contact with me.

## License ##

MIT License.
