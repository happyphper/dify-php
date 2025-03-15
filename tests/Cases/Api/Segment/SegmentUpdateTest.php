<?php

namespace Happyphper\Dify\Tests\Cases\Api\Segment;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\Requests\SegmentUpdateRequest;
use Happyphper\Dify\Public\Responses\Segment;
use Happyphper\Dify\Tests\Cases\Api\TestCase;
use Happyphper\Dify\Tests\Cases\Api\Traits\SegmentTestTrait;

class SegmentUpdateTest extends TestCase
{
    use SegmentTestTrait;

    /**
     * 测试更新分段内容
     *
     * @group api
     * @throws ApiException
     */
    public function testUpdateContent()
    {
        // 创建测试分段
        $segmentId = $this->createTestSegment('原始分段内容', '原始答案', ['原始']);

        // 更新分段
        $updateRequest = new SegmentUpdateRequest();
        $updateRequest->content = '更新后的分段内容';
        $updateRequest->answer = '更新后的答案';
        $updateRequest->keywords = ['更新'];

        $updateRes = $this->client->segments()->update($this->dataset->id, $this->docCreateRes->document->id, $segmentId, $updateRequest);
        $updatedSegment = $updateRes->data[0];

        // 验证更新结果
        $this->assertIsArray($updateRes->data);
        $this->assertCount(1, $updateRes->data);
        $this->assertInstanceOf(Segment::class, $updatedSegment);
        $this->assertEquals($segmentId, $updatedSegment->id);
        $this->assertEquals('更新后的分段内容', $updatedSegment->content);
        $this->assertEquals(['更新'], $updatedSegment->keywords);
    }

    /**
     * 测试更新分段状态
     *
     * @group api
     * @throws ApiException
     */
    public function testUpdateStatus()
    {
        // 创建测试分段
        $segmentId = $this->createTestSegment('测试分段内容', '测试答案');

        // 更新分段状态
        $updateRequest = new SegmentUpdateRequest();
        $updateRequest->content = '测试分段内容';
        $updateRequest->answer = '测试答案';
        $updateRequest->enabled = false;

        $updateRes = $this->client->segments()->update($this->dataset->id, $this->docCreateRes->document->id, $segmentId, $updateRequest);
        $updatedSegment = $updateRes->data[0];

        // 验证更新结果
        $this->assertIsArray($updateRes->data);
        $this->assertCount(1, $updateRes->data);
        $this->assertInstanceOf(Segment::class, $updatedSegment);
        $this->assertEquals($segmentId, $updatedSegment->id);
        $this->assertFalse($updatedSegment->enabled);
        $this->assertNotNull($updatedSegment->disabledAt);
    }

    /**
     * 测试重新生成子分段
     *
     * @group api
     * @throws ApiException
     */
    public function testRegenerateChildChunks()
    {
        // 创建测试分段
        $segmentId = $this->createTestSegment('测试分段内容', '测试答案');

        // 更新分段并重新生成子分段
        $updateRequest = new SegmentUpdateRequest();
        $updateRequest->content = '更新后的测试分段内容';
        $updateRequest->answer = '更新后的测试答案';
        $updateRequest->regenerateChildChunks = true;

        $updateRes = $this->client->segments()->update($this->dataset->id, $this->docCreateRes->document->id, $segmentId, $updateRequest);
        $updatedSegment = $updateRes->data[0];

        // 验证更新结果
        $this->assertIsArray($updateRes->data);
        $this->assertCount(1, $updateRes->data);
        $this->assertInstanceOf(Segment::class, $updatedSegment);
        $this->assertEquals($segmentId, $updatedSegment->id);
        $this->assertEquals('更新后的测试分段内容', $updatedSegment->content);
        // $this->assertEquals('更新后的测试答案', $updatedSegment->answer);
        $this->assertEquals('completed', $updatedSegment->status);
    }
}
