# EchoTracker

Is your socials communities growing?
EchoTracker is a tool that helps you to track the number of followers.

## Track your socials communities

```shell
php echo-tracker app:count-followers <configuration_path>
```

First time execution will help you to create the configuration file by asking you the required information.

Then you can simply run the command to track the number of followers.

```shell
php echo-tracker app:count-followers ./socials.json
```

All socials trackers could be called individually with their own command and arguments.

```shell
php echo-tracker app:count-<social-media>-followers <...arguments>
```

Get the full list with the command `php echo-tracker`.

### BlueSky

The command will fail if the username/password require a 2FA code.

- username: your blue sky username
- password: your blue sky password
- account: the account you want to track

### Discord

- invitation_link: the discord server invitation link, without expiration date, you want to track

### Instagram

- username: the instagram username you want to track

### Twitch

- client_id: your twitch client id
- client_secret: your twitch client secret
- broadcaster: Twitch broadcaster you want to track

### Twitter

- client_id: your twitch client id
- client_secret: your twitch client secret
- account: Twitter account you want to track

## Build

```shell
php echo-tracker app:build echo-tracker
```
