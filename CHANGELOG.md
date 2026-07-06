# Changelog

All notable changes to this project will be documented in this file.

## [0.23.0-rc.2](https://github.com/tomylana93/nova-helpdesk/compare/v0.23.0-rc.1...v0.23.0-rc.2) (2026-07-06)


### Documentation

* **release:** document local deploy workflow ([fb46a82](https://github.com/tomylana93/nova-helpdesk/commit/fb46a823d7e04df5f6e21cc463df347b566c7248))

## [0.23.0-rc.1](https://github.com/tomylana93/nova-helpdesk/compare/v0.22.1-rc.1...v0.23.0-rc.1) (2026-07-06)


### Features

* add asset management module ([6908b30](https://github.com/tomylana93/nova-helpdesk/commit/6908b3067bd2cc2a7301c422b98f8d45ec3681b4))
* add asset management module ([57eb7b8](https://github.com/tomylana93/nova-helpdesk/commit/57eb7b887435c79abafd7e15f4d98be75c909ca4))
* add notifications system with real-time updates ([cc0e392](https://github.com/tomylana93/nova-helpdesk/commit/cc0e392ea8f4b0004d14059243f16bf370ea508f))
* add semantic versioning and app:bump-version command ([69e7af9](https://github.com/tomylana93/nova-helpdesk/commit/69e7af9cbf457c4e04bcd67762ee6b30ddb3e856))
* add semantic versioning and app:bump-version command ([62ffe5c](https://github.com/tomylana93/nova-helpdesk/commit/62ffe5c4125e9951647406a433f7f88e762bb7da))
* add ticket management features including approval, comments, and ticket details view ([9234960](https://github.com/tomylana93/nova-helpdesk/commit/9234960e21d4871d8b478aef3f25e77262936ded))
* **admin:** add user branch department table filters ([58e8f15](https://github.com/tomylana93/nova-helpdesk/commit/58e8f151acded43b48a5f27a01c4fdb8a3f8dde3))
* **audit:** add helpdesk audit documentation highlighting approval flow bugs and validation issues ([907c4ea](https://github.com/tomylana93/nova-helpdesk/commit/907c4ead8a6899b8c9c35f931b28cfe9694cf3b4))
* **auth:** require password change on next login and harden user role rules ([879fddf](https://github.com/tomylana93/nova-helpdesk/commit/879fddfee9fde9475fac6f189da8123d0a45fd58))
* **dashboard:** add AdminDashboard builder ([4118334](https://github.com/tomylana93/nova-helpdesk/commit/4118334f7b0a9ba38abb951834027ce4ba6133b0))
* **dashboard:** add AgentDashboard builder ([f01e956](https://github.com/tomylana93/nova-helpdesk/commit/f01e956a7db0f14d13aefca752a389798f8dfd5f))
* **dashboard:** add BreakdownDonut component ([f0d784e](https://github.com/tomylana93/nova-helpdesk/commit/f0d784e9d3a4b4d52ce117210c08dbb551546a8d))
* **dashboard:** add dashboard i18n keys (en/id) ([d576035](https://github.com/tomylana93/nova-helpdesk/commit/d57603570424d9fb72a442ba85c0ba69cdea72a3))
* **dashboard:** add dashboard prop types ([b0bfe30](https://github.com/tomylana93/nova-helpdesk/commit/b0bfe30df81c1e49caae534b8d3d905516bcb6fd))
* **dashboard:** add DashboardPeriod value object ([0572ab6](https://github.com/tomylana93/nova-helpdesk/commit/0572ab634c8c3d4a16a51020e0a055e589745ccb))
* **dashboard:** add Delta calculator ([12db56c](https://github.com/tomylana93/nova-helpdesk/commit/12db56caf6b5999a536ca876967cb3d7b2c32d95))
* **dashboard:** add DeltaBadge component ([25c3223](https://github.com/tomylana93/nova-helpdesk/commit/25c322340b0a5b28ef18ab12e1e62fa5d95cd5ea))
* **dashboard:** add driver-aware TicketMetricQueries ([9d9e4d6](https://github.com/tomylana93/nova-helpdesk/commit/9d9e4d6dcfd2cf2354b0623a20de855a414ac879))
* **dashboard:** add GetDashboardData orchestrator ([53c8ed1](https://github.com/tomylana93/nova-helpdesk/commit/53c8ed1c617359793e6dbe5fde79371042ab7d8a))
* **dashboard:** add MetricCard and SlaGauge components ([2e50606](https://github.com/tomylana93/nova-helpdesk/commit/2e506061c25e3ce2992957411cadefb0096c9e53))
* **dashboard:** add RequesterDashboard builder ([09f7cbc](https://github.com/tomylana93/nova-helpdesk/commit/09f7cbc3a23e19cb48b609aaa1ebb8298726cfb5))
* **dashboard:** add TicketStatus::activeCases helper ([efbb9df](https://github.com/tomylana93/nova-helpdesk/commit/efbb9df9801da221e8c3abee3ad3e7ca25ee8c55))
* **dashboard:** add TrendChart and PeriodControl components ([834651b](https://github.com/tomylana93/nova-helpdesk/commit/834651bd14e3ee33a44295f57857805f2fd25e90))
* **dashboard:** add useDashboard composable ([4e6872d](https://github.com/tomylana93/nova-helpdesk/commit/4e6872daf57c251cc43d56f1346d3e15937914af))
* **dashboard:** implement role-based dashboard with charts, metrics, and SLA compliance radial gauge ([56e0a44](https://github.com/tomylana93/nova-helpdesk/commit/56e0a4465a934507f016194dc6fa32b41d65205d))
* **dashboard:** implement role-based dashboard with metrics and charts ([66749ec](https://github.com/tomylana93/nova-helpdesk/commit/66749ec15a9897ae026b10b72f8bb48179cc3cd6))
* **dashboard:** rebuild dashboard page with period zones ([73b61ed](https://github.com/tomylana93/nova-helpdesk/commit/73b61ed4b69257db75a6d85c9f42ba45b6501efe))
* **dashboard:** rebuild dashboard with period zones, trends & i18n (v0.13.0) ([6a5868e](https://github.com/tomylana93/nova-helpdesk/commit/6a5868e845ad9fbeaeefc18f6ba1a3f9169ad7d2))
* **dashboard:** validate period query and render new payload ([9866ab4](https://github.com/tomylana93/nova-helpdesk/commit/9866ab42d19c5fbcd83c347ba40630811c1a6c44))
* enhance ticket creation and management with category and department handling ([992f390](https://github.com/tomylana93/nova-helpdesk/commit/992f3906ca2c23cfe459c1c04ebcae62b04ccd19))
* **helpdesk:** add read-only Auditor role with ticket creation ([f13d019](https://github.com/tomylana93/nova-helpdesk/commit/f13d01953008d5f8685a05cf0435eb1b2d48163f))
* **helpdesk:** add read-only Auditor role with ticket creation (v0.17.0) ([39b4a93](https://github.com/tomylana93/nova-helpdesk/commit/39b4a93c08eb6eca6f4ff30a6d2f50a9525a8af5))
* **helpdesk:** add ticket number sequencing and first response tracking ([7d6c202](https://github.com/tomylana93/nova-helpdesk/commit/7d6c20279fc03fd154badba6d53a9d705df9a30c))
* **helpdesk:** implement ticket and comment attachments support ([7c107ee](https://github.com/tomylana93/nova-helpdesk/commit/7c107ee150e82213ee55d3b5a17f8114158c58d2))
* **helpdesk:** implement ticket and comment attachments support ([7b76c1d](https://github.com/tomylana93/nova-helpdesk/commit/7b76c1d3acc4ffb8760c41c272bbf2a839b9b923))
* **helpdesk:** ticket number sequencing and first response tracking (v0.12.0) ([29624e9](https://github.com/tomylana93/nova-helpdesk/commit/29624e906471ab549199891125b34878c3bd749e))
* implement notification system for unassigned ticket creation and enhance channel authorization ([7b948a5](https://github.com/tomylana93/nova-helpdesk/commit/7b948a59cadda13132c178b42dd6fadfd24d6980))
* IT helpdesk system — full implementation & refactor ([ab8a7e0](https://github.com/tomylana93/nova-helpdesk/commit/ab8a7e04c0876146ef1af2d659219d5a753faf22))
* **lang:** add Indonesian translations for settings section ([2877d62](https://github.com/tomylana93/nova-helpdesk/commit/2877d627f3586a6a2004de83fd77d8d553895849))
* **master-data:** add import master data command and ignore csv files ([801ae64](https://github.com/tomylana93/nova-helpdesk/commit/801ae644dad1e021e25d6fca48e72d281a50e1b4))
* **master-data:** add SLA policy import support ([5f16097](https://github.com/tomylana93/nova-helpdesk/commit/5f160977a047e5a079d2e94f480e07b0c25c7265))
* **release:** add auto mode for bump version and conventions memory ([7a5e801](https://github.com/tomylana93/nova-helpdesk/commit/7a5e801889815ceec663c9eb3472fe69f17e7b93))
* **release:** add auto mode for bump version and conventions memory ([ecb059a](https://github.com/tomylana93/nova-helpdesk/commit/ecb059acca21c531781f6b2d54858779e20b173f))
* **release:** automate changelog generation and add README ([4e22640](https://github.com/tomylana93/nova-helpdesk/commit/4e22640c34531c63d4d41e5fd751bd16768706d8))
* **release:** implement automated changelog and add README ([6718f12](https://github.com/tomylana93/nova-helpdesk/commit/6718f123faeb6014a02742f32069181feb4078e6))
* **reports:** add operational reporting exports ([d3d5a09](https://github.com/tomylana93/nova-helpdesk/commit/d3d5a093b879d6d7f4dabd16254ad6dc91dc1ca7))
* **reports:** add operational reporting exports ([603751a](https://github.com/tomylana93/nova-helpdesk/commit/603751a4d2fe9112a9ec2d99037d23cb7b03f5dd))
* **reports:** dynamically localize dates in Excel exports to the user's timezone ([d244e1d](https://github.com/tomylana93/nova-helpdesk/commit/d244e1d35c9a82a6f7f3bc72dbfb4fe2f75c72a5))
* **reports:** dynamically localize dates in Excel exports to the user's timezone ([77d87f4](https://github.com/tomylana93/nova-helpdesk/commit/77d87f4d4957403fc9f1a64948d6d625eb31fb8b))
* **reports:** group category filter by parent and fix filter layout ([00f7dc0](https://github.com/tomylana93/nova-helpdesk/commit/00f7dc09b4dfba3c39d70ce377600b622a42056b))
* **reports:** group category filter by parent and fix filter layout ([da00d16](https://github.com/tomylana93/nova-helpdesk/commit/da00d16cb7ba56215793f7d0c316e2366931dbd9))
* **reports:** structure category option list identical to create ticket ([39d895c](https://github.com/tomylana93/nova-helpdesk/commit/39d895cd7c60f19a077253098c9ce83e8defca9c))
* **reports:** structure category option list identical to create ticket ([4de8b07](https://github.com/tomylana93/nova-helpdesk/commit/4de8b074a6d15902817bbcade742bc2fb514f172))
* **sla:** exclude weekends, public holidays, and lunch breaks from SLA calculations ([76cfb55](https://github.com/tomylana93/nova-helpdesk/commit/76cfb555c68fef41b46e376e314f4792dd8e382d))
* **sla:** exclude weekends, public holidays, and lunch breaks from SLA calculations ([1e79941](https://github.com/tomylana93/nova-helpdesk/commit/1e79941a07618b92ba539e44a2009e9069f1f30a))
* streamline SLA policy options and enhance datatable translations for priorities ([6e52856](https://github.com/tomylana93/nova-helpdesk/commit/6e52856480cb90dc2fb51f2632ef6a847998ecbb))
* **tickets:** show remaining sla in ticket table ([92737e1](https://github.com/tomylana93/nova-helpdesk/commit/92737e1391f76e0bf317c7544ab63da96704ba80))


### Bug Fixes

* add 'opencode' to agents list in boost.json and format .mcp.json for consistency ([9722d9c](https://github.com/tomylana93/nova-helpdesk/commit/9722d9cf0703e208ed683bfd4cba35b86da86206))
* **dashboard:** align SLA gauge card header with other metric cards ([755262c](https://github.com/tomylana93/nova-helpdesk/commit/755262ca7e2d5ee309e68e40d0b0fb3c917ec091))
* **dashboard:** constrain donut size so SLA gauge and breakdown render correctly ([26d9df3](https://github.com/tomylana93/nova-helpdesk/commit/26d9df3d8e439426f12bab5d8eb26d7ee0b48ab9))
* **dashboard:** resolve HelpCircle warning and missing SLA compliance props crash ([a376c77](https://github.com/tomylana93/nova-helpdesk/commit/a376c77ca2538c574d5f1e54b217e12f01a7aac5))
* **dashboard:** SLA gauge sizing & header consistency (v0.13.1) ([9938d92](https://github.com/tomylana93/nova-helpdesk/commit/9938d92c7a8ed0ca25342d364db3c5b3c2049af9))
* **helpdesk:** drop manage-assets permission from it_agent role ([08b2cb7](https://github.com/tomylana93/nova-helpdesk/commit/08b2cb7a15a21b0f3fbe186bd259339567951796))
* **helpdesk:** resolve ticket assets serialization nested data wrapper bug ([8418004](https://github.com/tomylana93/nova-helpdesk/commit/8418004ced00c64fea8a68aafdcecd5df510d73e))
* **reports:** make parent categories not selectable in reports filter ([6ab9011](https://github.com/tomylana93/nova-helpdesk/commit/6ab901170993c2faab876808488f9e07694c43cf))
* **reports:** make parent categories not selectable in reports filter ([4c8a667](https://github.com/tomylana93/nova-helpdesk/commit/4c8a667b5f886bbae5de78ef03f95e8228ff04b9))
* resolve audit findings and enhance ticket approval process with validations ([01817c5](https://github.com/tomylana93/nova-helpdesk/commit/01817c5bc210251d3aa3db4c04e1bc44cb0683b1))
* **settings:** update site name and description to reflect correct branding ([d90a4ed](https://github.com/tomylana93/nova-helpdesk/commit/d90a4ed86d54c92b5804a17f61b2e49d14122669))
* **tickets:** auto-populate submitted_at and fix SLA due dates crash ([bee7709](https://github.com/tomylana93/nova-helpdesk/commit/bee7709ce41f1d3fada769f89480b16d83c99e0e))
* update category placeholder text to remove optional indication ([47f2542](https://github.com/tomylana93/nova-helpdesk/commit/47f2542c8c6e47cce44f64469cfd3ed9d41144a1))
* update project name from "nova-core" to "nova-helpdesk" ([af21c2b](https://github.com/tomylana93/nova-helpdesk/commit/af21c2ba1412d6d830fd3b8e4613276b44eb70f5))
* update shadcnVue command from npx to pnpm for consistency ([a9aca0d](https://github.com/tomylana93/nova-helpdesk/commit/a9aca0d327fe41a740eef1b19af2ca8ed807f41c))


### Refactors

* complete IT helpdesk flow refactor (phases 1-8) ([2d64117](https://github.com/tomylana93/nova-helpdesk/commit/2d64117ed037e1ef03381e4409d3eab1eb6aa322))
* complete IT helpdesk flow refactor (phases 1-8) ([55d0fb2](https://github.com/tomylana93/nova-helpdesk/commit/55d0fb27a3dfd390b8266288384ba60ff6d58299))
* Consolidate migrations ([d8cc5e6](https://github.com/tomylana93/nova-helpdesk/commit/d8cc5e6c34cc7ece5b1472b16fb1e9838960ebcc))
* **dashboard:** extract query and metrics compilation to GetDashboardMetrics Action ([1511652](https://github.com/tomylana93/nova-helpdesk/commit/15116522bfb0cbe2e6b860211ba87aa816bdfd2f))
* **database:** Consolidation of SLA and first_responded_at columns into create_tickets_table migration ([90bc11c](https://github.com/tomylana93/nova-helpdesk/commit/90bc11caadcf091d0f0bed79fd342e84289fed08))
* make app name dynamic and SSR-safe fallback to Nova Helpdesk ([b3c9954](https://github.com/tomylana93/nova-helpdesk/commit/b3c9954dfdaa8e7cfaf9e02bd298530ad1c9b860))
* make app name dynamic and SSR-safe fallback to Nova Helpdesk ([5ea03d5](https://github.com/tomylana93/nova-helpdesk/commit/5ea03d52b661e915c4db69d0e8b8abed3d674960))
* remove obsolete mcp.json configuration file ([09d8d9e](https://github.com/tomylana93/nova-helpdesk/commit/09d8d9e0acec4cd14d50d44364723dd882652767))
* **style:** update favicon fallbacks to custom assets ([18e94ac](https://github.com/tomylana93/nova-helpdesk/commit/18e94aca6df557334b46ba0fe15d79f2162652a5))
* **style:** update favicon fallbacks to custom assets ([21471e2](https://github.com/tomylana93/nova-helpdesk/commit/21471e2673fcdca4d2fe77925e04862d18757a70))
* **tests:** remove ImportMasterDataCommandTest as it is no longer needed ([209f879](https://github.com/tomylana93/nova-helpdesk/commit/209f87938af6648e7b0ba042ecefc67aa6fc0f7b))
* **ui:** address responsive review findings ([979cbc6](https://github.com/tomylana93/nova-helpdesk/commit/979cbc695bc839d78059fff4a45aa60eddea9246))
* **ui:** responsive dashboard period control and gutters ([0bb6be8](https://github.com/tomylana93/nova-helpdesk/commit/0bb6be85bf1555798f2eb6ee77678cf80393325a))
* **ui:** responsive polish across archetypes (v0.17.1) ([0a90c6d](https://github.com/tomylana93/nova-helpdesk/commit/0a90c6d820abccd77103c2aca0a86c0bf72a278f))
* **ui:** responsive settings layouts and pickers ([a409769](https://github.com/tomylana93/nova-helpdesk/commit/a4097698f943171a5847f77a293d7e1c85613388))
* **ui:** stack detail definition lists on mobile ([669aef0](https://github.com/tomylana93/nova-helpdesk/commit/669aef0245c2ecfbb2e479cbe074d3e759997dcd))
* update Serena MCP configuration to remote and adjust context settings ([591a229](https://github.com/tomylana93/nova-helpdesk/commit/591a2298720e655b71fb51ce47e13b1e8d42d81a))


### Documentation

* add CONTRIBUTING, SECURITY, env guide, and ticket flow diagram ([300216a](https://github.com/tomylana93/nova-helpdesk/commit/300216a6f21cfe6fb9deee5360a7fdca9f05d233))
* add dark-mode responsive logo and badges to README ([da06048](https://github.com/tomylana93/nova-helpdesk/commit/da06048b93a67a7dd1d13318a6c10397e0e57f48))
* add dark-mode responsive logo and badges to README ([ad4c26b](https://github.com/tomylana93/nova-helpdesk/commit/ad4c26b7e0159d360f501bb2caf5cc2894b046d9))
* add responsive refactor design spec ([8031b50](https://github.com/tomylana93/nova-helpdesk/commit/8031b502301e9d108bfb9c586c9956fd8a1d9d9f))
* add responsive refactor implementation plan ([b47296a](https://github.com/tomylana93/nova-helpdesk/commit/b47296a1e8054dcfcd82ad484fae42349dadb2c7))
* add ticket lifecycle flow, env documentation, CONTRIBUTING.md, and SECURITY.md ([1e235cc](https://github.com/tomylana93/nova-helpdesk/commit/1e235cc62921973c5f0fbb6a060e6a9e7e233d7a))
* document immutable release flow ([e50fbea](https://github.com/tomylana93/nova-helpdesk/commit/e50fbeaba2357d0a175f8d1aa129262c3c84ed7e))
* document immutable release flow ([57da83e](https://github.com/tomylana93/nova-helpdesk/commit/57da83e9f557ed6b17715fb37b22bb3e2d17b5a9))
* make GitHub Actions status badge style uniform ([4638950](https://github.com/tomylana93/nova-helpdesk/commit/4638950fde9e5248bd0e42a88a1a3259fed13d30))
* make GitHub Actions status badge style uniform ([3d32f9d](https://github.com/tomylana93/nova-helpdesk/commit/3d32f9db5d2d1b6a9c6f004c188d85dd5c514401))
* **memory:** add git publish handoff workflow ([3009ab9](https://github.com/tomylana93/nova-helpdesk/commit/3009ab916544ffe0e95ca74ffae1bac9295178f1))
* remove Laravel Pint badge and add MIT License ([61fb4c3](https://github.com/tomylana93/nova-helpdesk/commit/61fb4c3007e3f0aa2d474c0875e16daf84fa4a9a))
* remove Pint badge and add MIT License ([892ebc3](https://github.com/tomylana93/nova-helpdesk/commit/892ebc3e3853f3a3af91fd58565ce6cbfa037b2e))
* **repo:** clean up stale memory and documentation references ([02176d0](https://github.com/tomylana93/nova-helpdesk/commit/02176d0721eb9ddb0af92df7c42a3bb9eb1014f3))
* update backend documentation for admin, core, and helpdesk sections ([9d3e4f7](https://github.com/tomylana93/nova-helpdesk/commit/9d3e4f742fa86f436175afe33daefa44a2d76f0e))
* update deploy handoff flow ([33a4cf1](https://github.com/tomylana93/nova-helpdesk/commit/33a4cf1ecad234749b078aad802644e4243e8a5d))
* use slightly rounded style for all badges ([2644b16](https://github.com/tomylana93/nova-helpdesk/commit/2644b166c289372b51b78c9af1317f98fa09f9a7))
* use slightly rounded style for all badges ([195c64a](https://github.com/tomylana93/nova-helpdesk/commit/195c64ac58a0826496f372955f645c7c144aa905))

## [0.23.0](https://github.com/tomylana93/nova-helpdesk/compare/v0.22.1...v0.23.0) (2026-07-06)


### Features

* **auth:** require password change on next login and harden user role rules ([879fddf](https://github.com/tomylana93/nova-helpdesk/commit/879fddfee9fde9475fac6f189da8123d0a45fd58))


### Refactors

* update Serena MCP configuration to remote and adjust context settings ([591a229](https://github.com/tomylana93/nova-helpdesk/commit/591a2298720e655b71fb51ce47e13b1e8d42d81a))

## [0.22.1] - 2026-06-15

### Fixed
- fix(helpdesk): resolve ticket assets serialization nested data wrapper bug

### Other Changes
- Merge pull request #40 from tomylana93/dev
- docs: document immutable release flow
- Merge pull request #39 from tomylana93/dev

## [0.22.0] - 2026-06-12

### Added
- feat: add asset management module

### Other Changes
- Merge pull request #38 from tomylana93/dev

## [0.21.0] - 2026-06-12

### Added
- feat(reports): dynamically localize dates in Excel exports to the user's timezone

### Other Changes
- Merge pull request #37 from tomylana93/dev

## [0.20.0] - 2026-06-12

### Added
- feat(sla): exclude weekends, public holidays, and lunch breaks from SLA calculations

### Other Changes
- Merge pull request #36 from tomylana93/dev

## [0.19.0] - 2026-06-12

### Added
- feat(reports): structure category option list identical to create ticket

### Other Changes
- Merge pull request #35 from tomylana93/dev

## [0.18.1] - 2026-06-12

### Fixed
- fix(reports): make parent categories not selectable in reports filter

### Other Changes
- Merge pull request #34 from tomylana93/dev

## [0.18.0] - 2026-06-12

### Added
- feat(reports): group category filter by parent and fix filter layout

### Other Changes
- Merge pull request #33 from tomylana93/dev

## [0.17.1] - 2026-06-12

### Changed & Refactored
- refactor(ui): address responsive review findings
- refactor(ui): responsive settings layouts and pickers
- refactor(ui): stack detail definition lists on mobile
- refactor(ui): responsive dashboard period control and gutters

### Other Changes
- style(ui): prettier reflow after detail list responsive edits
- docs: add responsive refactor implementation plan
- docs: add responsive refactor design spec
- Merge pull request #32 from tomylana93/dev

## [0.17.0] - 2026-06-12

### Added
- feat(helpdesk): add read-only Auditor role with ticket creation

### Other Changes
- Merge pull request #31 from tomylana93/dev

## [0.16.0] - 2026-06-12

### Added
- feat(reports): add operational reporting exports

### Other Changes
- Merge pull request #30 from tomylana93/dev
- docs: update deploy handoff flow
- Merge pull request #29 from tomylana93/dev

## [0.15.0] - 2026-06-12

### Added
- feat(admin): add user branch department table filters

### Other Changes
- Merge pull request #28 from tomylana93/dev

## [0.14.1] - 2026-06-12

### Changed & Refactored
- refactor(style): update favicon fallbacks to custom assets

### Other Changes
- Merge pull request #27 from tomylana93/dev

## [0.14.0] - 2026-06-12

### Added
- feat(helpdesk): implement ticket and comment attachments support

### Other Changes
- docs(repo): clean up stale memory and documentation references
- Merge pull request #26 from tomylana93/dev

## [0.13.7] - 2026-06-12

### Changed & Refactored
- refactor(database): Consolidation of SLA and first_responded_at columns into create_tickets_table migration

### Other Changes
- Merge branch 'dev' into main

## [0.13.6] - 2026-06-12

### Other Changes
- Implement HTML5 browser desktop notifications with cross-tab deduplication
- Merge branch 'dev' into main

## [0.13.5] - 2026-06-11

### Other Changes
- Untrack .deploy directory and add it to .gitignore
- Merge branch 'dev' into main

## [0.13.4] - 2026-06-11

### Other Changes
- Add Nginx and Supervisor configuration templates for Reverb and Queue deployment
- Merge branch 'dev' into main

## [0.13.3] - 2026-06-11

### Other Changes
- Convert notifications table notifiable morph to uuid
- Merge pull request #25 from tomylana93/dev
- fix media morph ids for uuid models
- Merge pull request #24 from tomylana93/dev

## [0.13.2] - 2026-06-11

### Other Changes
- Merge pull request #23 from tomylana93/dev
- fix deployment migration issues
- Merge pull request #22 from tomylana93/dev

## [0.13.1] - 2026-06-11

### Fixed
- fix(dashboard): align SLA gauge card header with other metric cards
- fix(dashboard): constrain donut size so SLA gauge and breakdown render correctly

### Other Changes
- Merge pull request #21 from tomylana93/dev

## [0.13.0] - 2026-06-11

### Added
- feat(dashboard): rebuild dashboard page with period zones
- feat(dashboard): add TrendChart and PeriodControl components
- feat(dashboard): add BreakdownDonut component
- feat(dashboard): add MetricCard and SlaGauge components
- feat(dashboard): add DeltaBadge component
- feat(dashboard): add useDashboard composable
- feat(dashboard): add dashboard prop types
- feat(dashboard): add dashboard i18n keys (en/id)
- feat(dashboard): validate period query and render new payload
- feat(dashboard): add GetDashboardData orchestrator
- feat(dashboard): add AdminDashboard builder
- feat(dashboard): add AgentDashboard builder
- feat(dashboard): add RequesterDashboard builder
- feat(dashboard): add driver-aware TicketMetricQueries
- feat(dashboard): add Delta calculator
- feat(dashboard): add DashboardPeriod value object
- feat(dashboard): add TicketStatus::activeCases helper

### Other Changes
- test(dashboard): use Date facade for test-now per rector
- Merge pull request #20 from tomylana93/dev

## [0.12.2] - 2026-06-11

### Fixed
- fix(settings): update site name and description to reflect correct branding

## [0.12.1] - 2026-06-11

### Other Changes
- Merge pull request #19 from tomylana93/dev
- style(frontend): fix prettier formatting on Dashboard.vue

## [0.12.0] - 2026-06-11

### Added
- feat(helpdesk): add ticket number sequencing and first response tracking

### Other Changes
- Merge pull request #18 from tomylana93/dev

## [0.11.1] - 2026-06-11

### Other Changes
- docs(memory): add git publish handoff workflow
- Merge pull request #17 from tomylana93/dev

## [0.11.0] - 2026-06-11

### Added
- feat(tickets): show remaining sla in ticket table

## [0.10.2] - 2026-06-11

### Fixed
- fix(tickets): auto-populate submitted_at and fix SLA due dates crash

### Other Changes
- Merge branch 'fix/ticket-submitted-at' into dev

## [0.10.1] - 2026-06-11

### Fixed
- fix(dashboard): resolve HelpCircle warning and missing SLA compliance props crash

### Other Changes
- Merge branch 'fix/dashboard-errors' into dev

## [0.10.0] - 2026-06-11

### Added
- feat(master-data): add SLA policy import support

### Other Changes
- Merge branch 'feat/import-sla-policies' into dev
- Merge pull request #16 from tomylana93/dev

## [0.9.0] - 2026-06-11

### Added
- feat(dashboard): implement role-based dashboard with charts, metrics, and SLA compliance radial gauge

### Changed & Refactored
- refactor(dashboard): extract query and metrics compilation to GetDashboardMetrics Action

### Other Changes
- Merge pull request #15 from tomylana93/dev
- Merge pull request #14 from tomylana93/dev
- docs: add ticket lifecycle flow, env documentation, CONTRIBUTING.md, and SECURITY.md
- Merge pull request #13 from tomylana93/dev
- docs: remove Laravel Pint badge and add MIT License
- Merge pull request #12 from tomylana93/dev
- docs: use slightly rounded style for all badges
- Merge pull request #11 from tomylana93/dev
- docs: make GitHub Actions status badge style uniform
- Merge pull request #10 from tomylana93/dev
- docs: add dark-mode responsive logo and badges to README
- Merge pull request #9 from tomylana93/dev

## [0.8.0] - 2026-06-11

### Added
- feat(release): implement automated changelog and add README
- feat(release): add auto mode for bump version and conventions memory
- feat: add semantic versioning and app:bump-version command
- feat: implement notification system for unassigned ticket creation and enhance channel authorization
- feat: enhance ticket creation and management with category and department handling
- feat: add notifications system with real-time updates
- feat(audit): add helpdesk audit documentation highlighting approval flow bugs and validation issues
- feat: streamline SLA policy options and enhance datatable translations for priorities
- feat(lang): add Indonesian translations for settings section
- feat: add ticket management features including approval, comments, and ticket details view
- feat(master-data): add import master data command and ignore csv files

### Fixed
- fix: update category placeholder text to remove optional indication
- fix: resolve audit findings and enhance ticket approval process with validations
- fix: add 'opencode' to agents list in boost.json and format .mcp.json for consistency
- fix: update shadcnVue command from npx to pnpm for consistency
- fix: update project name from "nova-core" to "nova-helpdesk"

### Changed & Refactored
- refactor: make app name dynamic and SSR-safe fallback to Nova Helpdesk
- refactor: complete IT helpdesk flow refactor (phases 1-8)
- refactor: remove obsolete mcp.json configuration file
- refactor(tests): remove ImportMasterDataCommandTest as it is no longer needed

### Other Changes
- Merge pull request #8 from tomylana93/dev
- Merge pull request #7 from tomylana93/dev
- Merge pull request #6 from tomylana93/dev
- Merge pull request #5 from tomylana93/dev
- Merge pull request #4 from tomylana93/refactor/helpdesk-flow
- Merge pull request #3 from tomylana93/dev
- docs: update backend documentation for admin, core, and helpdesk sections
- Refactor code structure for improved readability and maintainability
- Merge pull request #2 from tomylana93/dev
- Add 'all_parents' label to English and Indonesian datatable language files
- Fix button layout alignment on master-data and settings index pages
- Implement Queue and Ticket Category CRUD master data
- Add serena and shadcn-vue configurations to mcp_config.json
- Add mcp_config.json for MCP server configuration and update boost.json to include 'antigravity' agent
- Update composer.lock to reflect dependency version changes for Laravel and Symfony packages
- Add settings.json for serena-hooks configuration
- Add PRD, task plan, and technical design documents for internal IT helpdesk project
- Add new skills for Pest testing, Tailwind CSS development, and Wayfinder integration; update guidelines and configuration files
- Refactor code structure for improved readability and maintainability
- remove UserSeederTest as UserSeeder is no longer used
- remove UserSeeder and update DatabaseSeeder to prevent seeding in production
- initial commit
