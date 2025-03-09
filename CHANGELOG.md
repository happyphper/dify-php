# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2024-03-09

### Fixed
- 修复了 `SegmentResponse` 类在处理更新响应时的类型错误
- 修复了删除不存在段落时的错误处理，现在正确返回 404 错误
- 改进了 `HttpClient` 类中的错误处理，确保 404 错误码以字符串形式传递

### Added
- 为 `SegmentUpdateRequest` 类添加了 `regenerateChildChunks` 参数支持
- 完善了单元测试覆盖率，添加了更多边界情况的测试用例

## [1.0.0] - 2023-03-07

### Added
- Initial release of the Dify PHP SDK
- Support for Hyperf framework
- Dataset operations (create, list, delete, retrieve)
- Document operations (create by text, create by file, list, update, delete)
- Segment operations (create, list, update, delete)
- Error handling with ApiException
- Comprehensive documentation in English and Chinese
- Examples for basic usage 
