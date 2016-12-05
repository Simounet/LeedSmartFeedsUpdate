# Leed Smart Feeds Update

## Description

This is a [Leed](https://github.com/ldleman/Leed)'s plugin to update feeds smartly. Without it, every single feed is checked on update. It is quite slow and useless for feeds that are not updated frequently.

## Requirements

You need to have a working copy of Leed. If not look at the [Leed's install instructions](https://github.com/ldleman/Leed#installation-1).


## How does it work?

Leed Smart Feeds Update get the frequencies' average of the last 10 feed's entries and store this value into the database. From now on, feeds will only be updated only once of its frenquency range. This process results on less feeds to update, quicker updates and less bandwitch used.

## Install

1. Do at least one first sync with the Leed original update system!
2. Download [LeedSmartFeedsUpdate](https://github.com/Simounet/LeedSmartFeedsUpdate/archive/dev.zip) into the `plugins` folder of Leed
3. Activate `LeedSmartFeedsUpdate` from the settings page
4. Change the Leed's crontab entry to do the update every minute
`* * * * * cd /var/www/leed && php action.php >> logs/cron.log 2>&1`

## Uninstall

1. Disable `LeedSmartFeedsUpdate` from the settings page
2. Remove the `LeedSmartFeedsUpdate` folder from `plugins`

## A feed's update happend to often/late. What can I do?

You can change the slot where the feed has been automatically placed from the `Smart feeds update settings` (uri: `/settings.php#smartFeedsUpdateSettingsBlock`) settings layout.

## What would be great to do next?

- Lock the update frequencies that the user changed even if we do a manual frequencies update
- Give to the user the ability to change the slot's frequencies from the settings' page
- Give to the user the ability to change the events limit used to calculate the frequencies' average

## Any idea or trouble?

- [Open an issue](https://github.com/Simounet/LeedSmartFeedsUpdate/issues/new)
- [Come to the #Leed IRC channel on Freenode](https://kiwiirc.com/client/irc.freenode.net/#Leed)
- [Mail](mailto:leedvibes@simounet.net)

## Licence

Leed Smart Feeds Update is under [MIT License](http://opensource.org/licenses/MIT).
