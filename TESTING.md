# Dify PHP SDK 测试指南

本文档介绍如何使用 Docker 运行 Dify PHP SDK 的单元测试，适用于宿主机没有 PHP 环境的情况。

## 前提条件

- 安装 [Docker](https://www.docker.com/get-started)

## 测试覆盖范围

SDK 的测试覆盖以下主要功能：

### 数据集操作
- 创建数据集
- 获取数据集列表
- 删除数据集
- 数据集检索

### 文档操作
- 通过文本创建文档
- 通过文件创建文档
- 获取文档列表
- 更新文档
- 删除文档

### 段落操作
- 创建段落
- 获取段落列表
- 更新段落
  - 基本更新操作
  - 重生成子段落
  - 错误处理（404等）
- 删除段落
  - 删除存在的段落
  - 删除不存在的段落（404错误处理）

### 错误处理
- API 异常处理
- 验证异常
- 认证异常
- 授权异常
- 404 未找到异常
- 速率限制异常
- 服务器异常

## 构建 Docker 镜像

首先，构建 Docker 镜像：

```bash
docker build -t dify-php-sdk .
```

## 运行测试

### 运行单元测试

我们使用 PHPUnit 进行单元测试，可以通过以下命令运行所有测试：

```bash
# 创建并运行容器
docker run --rm -it \
  -v $(pwd):/app \
  -w /app \
  --add-host=host.docker.internal:host-gateway \
  --name dify-php-test \
  dify-php-sdk \
  phpunit tests/Cases/Dataset/DatasetCreateTest.php
```

### 运行特定测试方法

如果你只想运行特定的测试方法，可以使用 `--filter` 参数：

```bash
docker run --rm -it \
  -v $(pwd):/app \
  -w /app \
  --add-host=host.docker.internal:host-gateway \
  --name dify-php-test \
  dify-php-sdk \
  phpunit --filter testBasicParams tests/Cases/Dataset/DatasetCreateTest.php
```

### 运行 API 测试

你可以运行特定的 API 测试方法：

```bash
docker run --rm -it \
  -v $(pwd):/app \
  -w /app \
  --add-host=host.docker.internal:host-gateway \
  --name dify-php-test \
  dify-php-sdk \
  phpunit --filter testCreateAndVerifyDataset tests/Cases/Dataset/DatasetCreateTest.php
```

### 启动交互式 Shell

如果你想在容器中进行更多的测试，可以启动一个交互式 Shell：

```bash
# 创建并运行容器
docker run --rm -it \
  -v $(pwd):/app \
  -w /app \
  --add-host=host.docker.internal:host-gateway \
  --name dify-php-shell \
  dify-php-sdk \
  bash
```

### 在宿主机上执行容器内命令

如果你已经启动了容器，可以使用 `docker exec` 在宿主机上执行容器内的命令：

#### 运行单元测试

```bash
docker exec -it dify-php-shell phpunit tests/Cases/Dataset/DatasetCreateTest.php
```

#### 运行特定测试方法

```bash
docker exec -it dify-php-shell phpunit --filter testBasicParams tests/Cases/Dataset/DatasetCreateTest.php
```

#### 运行 API 测试

```bash
docker exec -it dify-php-shell phpunit --filter testCreateAndVerifyDataset tests/Cases/Dataset/DatasetCreateTest.php
```

#### 检查 PHP 版本

```bash
docker exec -it dify-php-shell php -v
```

## 测试配置

### 环境变量配置

测试使用以下环境变量进行配置，这些变量已在 `phpunit.xml` 文件中定义：

- `DIFY_DATASET_KEY`: API 密钥，默认为 `dataset-ufldW3iEBZma9WfB0NF3C2HR`
- `DIFY_BASE_URL`: API 基础 URL，默认为 `http://host.docker.internal:5001/v1`

如果你需要修改这些配置，可以通过以下方式：

1. 编辑 `phpunit.xml` 文件中的环境变量配置：

```xml
<php>
    <env name="DIFY_DATASET_KEY" value="你的API密钥"/>
    <env name="DIFY_BASE_URL" value="你的API基础URL"/>
</php>
```

2. 在运行测试时通过 `-e` 参数覆盖环境变量：

```bash
docker run --rm -it \
  -v $(pwd):/app \
  -w /app \
  --add-host=host.docker.internal:host-gateway \
  -e DIFY_DATASET_KEY=你的API密钥 \
  -e DIFY_BASE_URL=你的API基础URL \
  --name dify-php-test \
  dify-php-sdk \
  phpunit tests/Cases/Dataset/DatasetCreateTest.php
```

## 注意事项

1. 单元测试不会实际调用 Dify API，它们只测试参数构建的逻辑是否正确。

2. API 测试会实际调用 Dify API，需要提供有效的 API 密钥和正确的 API 地址。

3. 在 Docker 容器中，要访问宿主机上运行的服务，需要使用 `host.docker.internal` 而不是 `localhost`。

4. 如果测试失败，仔细阅读错误信息，它会告诉你预期值和实际值之间的差异。

5. 使用 `docker exec` 命令可以在宿主机上执行容器内的命令，无需进入容器内部。 
