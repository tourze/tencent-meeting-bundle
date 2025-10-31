# 腾讯会议Bundle需求规范

## 原始需求

> **用户原始需求描述：**
> "我要实现一个Bundle，全面对接腾讯会议提供的能力，基本上就是通过调用腾讯会议的API接口，同步很多信息到本地，然后再各种分发处理吧。"
> 
> **核心要求：**
> - 将腾讯会议的复杂API封装成易用的PHP/Symfony服务
> - 实现事件驱动的数据分发处理，让业务系统能够响应会议事件
> - 为企业级应用提供稳定可靠的会议能力集成
> - 使用 HttpClientBundle 来封装API（避免第三方SDK的调试困难）
> - 腾讯会议配置使用实体存储，支持多个AppId
> - 同步腾讯会议API提供的所有数据到本地
> - 使用Symfony事件系统转换腾讯会议事件

## 概述

### 包的目的和价值主张
TencentMeetingBundle 旨在将腾讯会议的复杂REST API封装成易用的PHP/Symfony服务，通过HttpClientBundle实现稳定的API调用，提供完整的数据同步机制和事件驱动架构，让企业级应用能够轻松集成腾讯会议能力。

### 核心价值
- **API封装简化**：将复杂的腾讯会议API封装成直观的Symfony服务
- **多租户支持**：通过实体配置支持多个AppId，满足多租户场景
- **全量数据同步**：自动同步腾讯会议所有可用数据到本地数据库
- **事件驱动架构**：将Webhook事件转换为Symfony事件，支持业务系统响应
- **企业级可靠性**：提供重试、缓存、错误处理等企业级特性

### 目标用户
- PHP/Symfony开发者：需要快速集成腾讯会议能力到应用中
- 企业IT部门：将腾讯会议与OA、CRM、ERP等企业系统深度集成
- SaaS服务提供商：为多个客户提供会议功能，需要多租户支持

## 功能需求

### API客户端服务（基于HttpClientBundle）

> **实现提示**：参考 `API 文档/鉴权方式/` 目录了解JWT和OAuth2.0的具体实现
> - JWT鉴权：使用SecretId和SecretKey生成签名
> - OAuth2.0：标准授权流程，获取access_token后调用API
> - 签名算法：TC3-HMAC-SHA256，参考 `API 文档/签名校验/签名代码示例.md`

#### 普遍性需求
- Bundle必须使用 `\HttpClientBundle\HttpClientBundle` 作为HTTP客户端基础
- Bundle必须为每个API模块提供独立的Client服务：
  - `MeetingClient` - 会议管理（创建、修改、查询、取消会议）
  - `UserClient` - 用户管理（CRUD操作）
  - `DepartmentClient` - 部门管理
  - `RecordingClient` - 录制管理
  - `RoomClient` - 会议室管理
  - `VoteClient` - 投票管理
  - `DocumentClient` - 文档管理
  - `DashboardClient` - 仪表盘数据
  - `LiveClient` - 直播管理
  - `ControlClient` - 会中控制
- Bundle必须实现腾讯会议的签名算法（TC3-HMAC-SHA256）
- Bundle必须支持JWT和OAuth2.0两种鉴权方式
- Bundle必须提供统一的错误处理和异常转换机制

#### 事件驱动需求
- 当API调用失败时，Bundle必须自动重试（可配置重试次数和间隔）
- 当Token过期时，Bundle必须自动刷新Token
- 当达到API限频时，Bundle必须自动延迟请求

### 多租户配置管理

#### 普遍性需求
- Bundle必须提供 `TencentMeetingConfig` 实体来存储配置信息
- 配置实体必须包含：AppId、SecretId、SecretKey、鉴权类型、Webhook Token等字段
- Bundle必须支持同时管理多个AppId的配置
- Bundle必须提供配置选择器，允许在运行时切换不同的AppId配置

#### 状态驱动需求
- 当处于多租户模式时，Bundle必须根据上下文（如当前用户、域名等）自动选择对应的配置
- 当配置被禁用时，Bundle必须拒绝使用该配置进行API调用

### 数据同步服务

> **实现提示**：参考 `API 文档/REST APIs/` 各模块了解可同步的数据结构
> - 企业会议管理：会议列表、会议详情、参会成员
> - 企业用户管理：用户列表、用户详情、部门结构
> - 企业云录制：录制列表、录制地址、访问数据
> - 仪表盘：实时会议、即将召开、已结束会议

#### 普遍性需求
- Bundle必须提供以下实体来存储同步数据：
  - `Meeting`：会议信息（ID、Code、主题、开始/结束时间、状态等）
  - `MeetingUser`：参会用户（会议ID、用户ID、入会/离会时间、角色等）
  - `User`：企业用户（userid、uuid、姓名、邮箱、手机、部门等）
  - `Department`：部门结构（部门ID、名称、父部门、层级路径等）
  - `Recording`：录制文件（会议ID、文件URL、时长、大小、创建时间等）
  - `MeetingRoom`：会议室（房间ID、名称、容量、设备信息等）
  - `MeetingGuest`：会议嘉宾（会议ID、嘉宾信息、邀请状态等）
  - `MeetingVote`：投票信息（投票主题、选项、结果统计等）
  - `MeetingDocument`：会议文档（文档ID、名称、上传者、权限等）
  - `Role`：角色信息（角色ID、名称、权限列表等）
  - `Permission`：权限配置（用户权限、会议权限、资源配额等）
  - `Device`：设备信息（设备ID、激活码、所属会议室等）
  - `Layout`：会议布局（布局ID、模板配置、默认设置等）
  - `Background`：会议背景（背景ID、图片URL、使用范围等）

#### 事件驱动需求
- 当执行定时同步任务时，Bundle必须按优先级同步数据（会议>用户>部门>其他）
- 当接收到Webhook事件时，Bundle必须实时更新对应的本地数据
- 当数据同步失败时，Bundle必须记录失败原因并在下次重试

#### 条件性需求
- 如果配置了增量同步，Bundle必须只同步变更的数据
- 如果配置了全量同步，Bundle必须定期执行完整数据同步
- 如果检测到数据不一致，Bundle必须触发数据校正流程

### Webhook事件处理

> **实现提示**：参考 `API 文档/事件订阅（Webhook）/` 了解事件类型和格式
> - 会议事件：创建、更新、开始、结束、用户入会/离会等
> - 云录制事件：开始、暂停、恢复、停止、完成、失败
> - 应用管理事件：开通、停用、授权、取消授权
> - 签名校验：使用TOKEN和时间戳验证请求合法性

#### 普遍性需求
- Bundle必须提供 `WebhookController` 来接收腾讯会议的Webhook回调
- Bundle必须实现Webhook签名验证机制（参考 `API 文档/签名校验/签名校验.md`）
- Bundle必须将原始Webhook事件存储到 `WebhookEvent` 实体
- Bundle必须提供事件解析器，将JSON payload转换为强类型对象

#### 事件驱动需求
- 当接收到会议创建事件时，Bundle必须创建或更新本地Meeting记录
- 当接收到用户入会事件时，Bundle必须更新参会者列表
- 当接收到录制完成事件时，Bundle必须同步录制文件信息
- 当接收到任何Webhook事件时，Bundle必须触发对应的Symfony事件

### 事件分发系统

#### 普遍性需求
- Bundle必须为每种腾讯会议事件定义对应的Symfony事件类
- Bundle必须提供事件订阅者基类，简化业务系统的事件处理
- Bundle必须支持事件优先级和停止传播机制

#### 事件映射规则
- 会议事件 → `TencentMeeting\Event\Meeting\*Event`
- 用户事件 → `TencentMeeting\Event\User\*Event`
- 录制事件 → `TencentMeeting\Event\Recording\*Event`
- 应用事件 → `TencentMeeting\Event\Application\*Event`

## 非功能需求

### 性能要求
- API调用必须支持并发请求（通过HttpClient的异步特性）
- 数据同步必须支持批量操作，单次同步至少处理100条记录
- Webhook处理必须在100ms内返回响应（异步处理具体业务）
- 本地数据查询必须建立适当的索引

### 可靠性要求
- Bundle必须实现幂等性，重复的Webhook事件不会导致数据重复
- Bundle必须提供数据一致性检查工具
- Bundle必须记录所有API调用日志，便于问题排查
- Bundle必须支持优雅降级，API不可用时使用本地缓存数据

### 安全要求
- Bundle必须加密存储敏感配置（SecretKey等）
- Bundle必须验证所有Webhook请求的签名
- Bundle必须防止API密钥泄露到日志中
- Bundle必须支持配置IP白名单限制Webhook来源

### 可扩展性要求
- Bundle必须提供接口和抽象类，允许自定义实现
- Bundle必须支持自定义数据存储适配器
- Bundle必须支持自定义事件处理器
- Bundle必须支持插件机制扩展功能

## 集成需求

### 框架集成
- Bundle必须与Symfony 6.4+完全兼容
- Bundle必须支持Symfony的依赖注入容器
- Bundle必须集成Symfony的缓存组件
- Bundle必须集成Symfony的Messenger组件（异步处理）
- Bundle必须使用Doctrine ORM进行数据持久化
- Bundle必须使用HttpClientBundle作为HTTP客户端基础

### 命令行工具

#### 配置管理命令
- `tencent:meeting:config:create` - 创建新的腾讯会议配置
- `tencent:meeting:config:list` - 列出所有配置
- `tencent:meeting:config:show` - 显示配置详情
- `tencent:meeting:config:update` - 更新配置信息
- `tencent:meeting:config:delete` - 删除配置
- `tencent:meeting:config:test` - 测试配置连接性

#### 数据同步命令
- `tencent:meeting:sync:all` - 同步所有数据
- `tencent:meeting:sync:meeting` - 同步会议数据
- `tencent:meeting:sync:user` - 同步用户数据
- `tencent:meeting:sync:department` - 同步部门数据
- `tencent:meeting:sync:recording` - 同步录制数据
- `tencent:meeting:sync:room` - 同步会议室数据
- `tencent:meeting:sync:role` - 同步角色权限数据
- `tencent:meeting:sync:device` - 同步设备数据
- `tencent:meeting:sync:status` - 查看同步状态和进度

#### 会议管理命令
- `tencent:meeting:create` - 创建会议
- `tencent:meeting:list` - 列出会议
- `tencent:meeting:show` - 显示会议详情
- `tencent:meeting:update` - 更新会议信息
- `tencent:meeting:cancel` - 取消会议
- `tencent:meeting:end` - 结束会议
- `tencent:meeting:participants` - 查看参会者列表

#### 用户管理命令
- `tencent:user:create` - 创建用户
- `tencent:user:list` - 列出用户
- `tencent:user:show` - 显示用户详情
- `tencent:user:update` - 更新用户信息
- `tencent:user:delete` - 删除用户
- `tencent:user:import` - 批量导入用户
- `tencent:user:export` - 导出用户数据

#### 录制管理命令
- `tencent:recording:list` - 列出录制文件
- `tencent:recording:download` - 下载录制文件
- `tencent:recording:delete` - 删除录制文件
- `tencent:recording:share` - 修改共享设置
- `tencent:recording:stats` - 查看访问统计

#### Webhook管理命令
- `tencent:webhook:list` - 列出Webhook事件
- `tencent:webhook:show` - 显示事件详情
- `tencent:webhook:replay` - 重放Webhook事件
- `tencent:webhook:test` - 发送测试事件
- `tencent:webhook:stats` - 查看事件统计
- `tencent:webhook:clear` - 清理历史事件

#### 调试和维护命令
- `tencent:meeting:check` - 检查配置和连接状态
- `tencent:meeting:stats` - 查看使用统计
- `tencent:meeting:cache:clear` - 清理缓存
- `tencent:meeting:logs` - 查看操作日志
- `tencent:meeting:validate` - 验证数据一致性
- `tencent:meeting:repair` - 修复数据不一致

## 验收标准

### 测试覆盖
- 单元测试覆盖率必须达到90%以上
- 必须包含集成测试验证API调用
- 必须包含Webhook处理的功能测试
- 必须包含多租户场景的测试

### 文档要求
- 必须提供完整的使用文档，包括：
  - 快速开始指南（安装、配置、首次使用）
  - API客户端使用文档（每个Client的方法和参数）
  - 数据实体文档（字段说明、关联关系）
  - 事件列表和处理指南
- 必须提供API客户端的使用示例：
  - 创建和管理会议的完整流程
  - 用户和部门同步示例
  - 录制文件管理示例
- 必须提供事件处理的示例代码：
  - 订阅Symfony事件
  - 处理Webhook回调
  - 自定义事件处理器
- 必须提供故障排查指南：
  - 常见错误和解决方案
  - 日志分析方法
  - 性能优化建议
  - 数据一致性检查和修复

### 代码质量
- 必须通过PHPStan Level 8检查
- 必须符合PSR-12编码规范
- 必须提供类型声明和返回类型
- 必须实现适当的日志记录

### 功能完整性
- 必须支持腾讯会议API文档中的所有主要接口
- 必须支持所有Webhook事件类型
- 必须实现完整的数据同步机制
- 必须提供可用的多租户支持

## 技术实现指导

### API文档参考路径
> **重要**：实现时必须参考 `API 文档/` 目录下的详细文档

- **鉴权实现**：`API 文档/鉴权方式/`、`API 文档/企业 secret 鉴权.md`
- **API接口**：`API 文档/REST APIs/` 各子目录
- **Webhook事件**：`API 文档/事件订阅（Webhook）/`
- **错误处理**：`API 文档/错误码.md`、`API 文档/返回结果/`
- **开发调试**：`API 文档/开发者工具/`

### 关键技术点

#### 签名算法实现
```php
// TC3-HMAC-SHA256 签名算法核心步骤
1. 构造规范请求串（CanonicalRequest）
2. 构造待签字符串（StringToSign）
3. 计算签名（Signature）
4. 拼接Authorization头
```

#### 多租户配置切换
```php
// 通过ConfigSelector服务动态选择配置
interface ConfigSelectorInterface {
    public function select(array $context): TencentMeetingConfig;
}
```

#### 事件转换映射
```php
// Webhook事件 → Symfony事件
'meeting.created' => MeetingCreatedEvent::class
'meeting.started' => MeetingStartedEvent::class
'user.joined' => UserJoinedEvent::class
```

## 限制和约束

- 不包含UI组件，仅提供后端服务
- 不直接处理视频流或音频流
- 不实现会议客户端功能（仅管理API）
- 依赖腾讯会议服务的可用性
- 必须使用HttpClientBundle，不使用第三方SDK
- 配置必须通过实体存储，不使用YAML配置文件

## 未来扩展（不在当前范围）

- GraphQL API支持
- 会议数据分析和报表功能
- 自动化会议调度算法
- AI会议助手集成
- 会议质量监控和告警
- WebRTC集成实现会议客户端
- 多平台会议聚合（整合Zoom、Teams等）