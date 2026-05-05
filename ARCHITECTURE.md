# hypeinteractions — Architecture (Elgg 5.x)

## Overview

Replaces Elgg's built-in comments block and likes system with a richer
interactions panel supporting threaded comments, reactions (likes), and
a `RiverObject` entity that proxies river items for annotation purposes.

Elgg version: **5.x** (migrated from 4.x on 2026-05-05)

## Plugin Entry Point

`Bootstrap::class` registered as the plugin bootstrap. No `start.php`.

## Entity Types

| Type | Subtype | Class | Notes |
|------|---------|-------|-------|
| object | comment | `Comment` | Modern comment entity, extends `ElggComment` |
| object | river_object | `RiverObject` | Proxies river items for annotation |
| object | hjcomment | `Comment` | Legacy 3.x subtype kept for existing rows |
| object | hjstream | `RiverObject` | Legacy 3.x subtype kept for existing rows |

## Actions (declarative controller form)

| Action | Controller |
|--------|-----------|
| `comment/save` | `SaveCommentAction` |
| `stream/like` | `ToggleLikeAction` |
| `likes/add` | `LikeAction` |
| `likes/delete` | `UnlikeAction` |

## Routes

| Name | Path | Resource |
|------|------|----------|
| `edit:object:comment` | `/stream/edit/{guid}` | `interactions/edit` |
| `view:object:comment` | `/stream/view/{guid}` | `interactions/view` |
| `collection:object:comment` | `/stream/comments/{guid}/{comment_guid?}` | `interactions/comments` |
| `collection:annotation:likes` | `/stream/likes/{guid}` | `interactions/likes` |

## Events Registered (Bootstrap::init)

All registered via `elgg_register_event_handler()` — hooks merged into events in Elgg 5.x.

| Event | Type | Handler |
|-------|------|---------|
| `entity:url` | `object` | `Router` |
| `entity:icon:url` | `object` | `Router` |
| `comments` | `all` | `ReplaceCommentsBlock` |
| `container_logic_check` | `object` | `CanCommentOnComment` |
| `permissions_check` | `annotation` | `CanEditLikeAnnotation` |
| `register` | `menu:interactions` | `InteractionsMenu` |
| `register` | `menu:river` | `RiverMenu` |
| `register` | `menu:social` | `SocialMenu` |
| `created` | `river` | `CreateRiverObject` |
| `delete:after` | `river` | `DeleteRiverObject` |
| `update:after` | `river` | `SyncRiverObjectAccess` |
| `create` | `object` | `SubscribeToCommentNotifications` |
| `prepare` | `notification:create:object:comment` | `FormatCommentNotification` |
| `get` | `subscriptions` | `GetCommentSubscribers` |
| `likes:is_likable` | `object:river_object` | _(inline closure)_ |

## Service

`InteractionsService` registered in `elgg-services.php` under key `interactions`.
Retrieved via `InteractionsService::instance()` (manual service-facade pattern
— does not use the removed `Elgg\Traits\Di\ServiceFacade` trait).

Provides:
- `getStats(ElggEntity)` — comment count, like count, and user-liked flag
- `getLimit(bool)` — comments per page from plugin settings
- `calculateOffset(int, int, ?Comment)` — pagination offset for a target comment
- `getDefaultCollection(ElggEntity)` — returns a `DefaultCommentCollection`

## Dependencies

| Plugin | Constraint |
|--------|-----------|
| `hypelists` | `must_be_active: true`, `position: after` |

## Plugin Settings

| Key | Default |
|-----|---------|
| `max_comment_depth` | 1 |
| `comment_form_position` | after |
| `comments_order` | asc |
| `comments_load_style` | load_older |
| `comments_limit` | 3 |
| `comments_load_limit` | 20 |
| `default_expand` | false |

## Migration Notes (4.x → 5.x)

- All `elgg_register_plugin_hook_handler()` calls converted to `elgg_register_event_handler()`.
- All handler signatures updated from `\Elgg\Hook` to `\Elgg\Event`; `$hook->` → `$event->`.
- `elgg_trigger_plugin_hook()` calls replaced with `elgg_trigger_event_results()`.
- `InteractionsService` no longer injects `PluginHooksService`; uses global event functions directly.
- `Elgg\Traits\Di\ServiceFacade` trait removed; replaced with manual `instance()`/`name()` methods.
- `composer.json` PHP constraint updated to `>=8.2`; Elgg constraint to `^5.0`.
- `Comment::canComment()` signature updated to `(int $user_guid = 0): bool`.
- `Comment::getDisplayName()` and `RiverObject::getDisplayName()` updated to return `string`.
- Docker stack upgraded: PHP 7.4→8.2, MySQL 5.7→8.0, `ELGG_SITE_URL` set to internal hostname.
- `hypelists` dependency declared in `elgg-plugin.php` `plugin.dependencies`.
- Test suite adapted: `_elgg_services()->hooks` → `_elgg_services()->events` in `BootstrapTest`.
