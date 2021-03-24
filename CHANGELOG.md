# Changelog

All notable changes to `laravel-mailcoach-postmark-feedback` will be documented in this file

## 3.0.1 - 2021-03-24

- Handle SubscriptionChange event from Postmark

## 3.0.0 - 2021-03-10

- Support for Mailcoach v4

## 2.4.2 - 2020-12-07

- Mark webhook calls as processed when no send is found

## 2.4.1 - 2020-11-19

- Change configuration key to `postmark_feedback`

## 2.4.0 - 2020-11-19

- Add support for adding a Stream header by setting the `mailcoach.postmark.message_stream` config key

## 2.3.1 - 2020-09-28

- Add support for Stream webhooks

## 2.3.0 - 2020-09-24

- Tag a Mailcoach v3 compatible release

## 2.2.1 - 2020-09-09

- add support for Laravel 8

## 2.2.0 - 2020-09-27

- fire `WebhookCallProcessedEvent` after processing a webhook

## 2.1.1 - 2020-09-04

- fix time on feedback registration

## 2.1.0 - 2020-03-24

- add ability to use a custom queue connection

## 2.0.0 - 2020-03-10

- add support for Laravel 7 and Mailcoach 2

## 1.0.0 - 2020-02-27

- initial release
