# Messaging & Task Requirements (Phase 5 Outline)

These notes capture the requirements we will revisit in the collaboration-focused clone after Phase 6, but they guide what the notification center must support long term.

## 1. Messaging System Expectations
- Conversations view with sidebar listing threads + unread counts (reference Geraye messaging components under `resources/js/features/messaging`).
- Real-time-like updates delivered via database notifications + polling (no WebSockets yet).
- Each conversation emits notifications:
  - `NewMessageReceived` (sender name, preview, route to thread).
  - Optional email when offline (user preference).
- User preferences screen should allow toggling email vs in-app notifications per channel.

## 2. Task Delegation Requirements
- Tasks assigned between team members must:
  - Create a database notification for the assignee.
  - Optionally send an email reminder (daily digest + due reminders).
  - Expose upcoming tasks in the sidebar badges (counts similar to Geraye `myTasksCount`, `myTodoCount`).
- Background job (cron) sends reminders for overdue items (queue ready but can remain unimplemented until Phase 6).

## 3. Future Implementation Checklist
- [ ] Define notification classes (message received, task assigned, task reminder) in `app/Notifications` with both mail + database channels.
- [ ] Add Vue stores/composables to fetch message/task counts and wire them into the sidebar header badges.
- [ ] Extend Notification settings page (`/settings/profile`) with toggles for each channel.
- [ ] Reuse `NotificationBell` dropdown to surface latest message/task alerts.
- [ ] Document task workflows and notification triggers in the upcoming collaboration roadmap.

Keep this outline handy when cloning the project for the boilerplate so Phase 7+ collaboration work slots in quickly.
