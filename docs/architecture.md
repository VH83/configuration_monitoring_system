# SyncWorks architecture

```text
 watched files -> config:watch -> safe reader -> parser resolver -> diff engine
                                                               |          |
                                                               + snapshot + ChangeEvent
                                                                               |
                                                                        database/Redis queue
                                                                               |
                                                                  PublishChangeEvent
                                                                               |
                                                                      WebhookNotifier
                                                                               |
                                                                demo or external receiver
```

The watcher owns detection and persistence only. Queue workers own delivery. Events and snapshots are append-oriented so delivery can be retried or replayed without rereading a changed file.