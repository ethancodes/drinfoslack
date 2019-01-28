# drinfoslack
Send Drush Core and Module update information to Slack

Each server gets a copy of this code.

Copy the example config and modify to include the sites you want.

Runs Drush for each site, aggregates a report, sends to Slack.

Using a variation of [this](https://gist.github.com/alexstone/9319715) to send to Slack.

## @TODO

- ~~Rename `config.json` to `config.example.json` so that the config files on servers do not make a mess every time we `git pull`~~
- ~~Check if one of the updates is `drupal` and include that in the message~~
- ~~Change cron timing so that LAMP11 runs at 0711, LAMP12 runs at 0712, etc~~
