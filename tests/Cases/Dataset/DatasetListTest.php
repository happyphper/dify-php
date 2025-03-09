<?php

declare(strict_types=1);

namespace Happyphper\Dify\Tests\Cases\Dataset;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Tests\Cases\TestCase;
use Happyphper\Dify\Responses\DatasetListResponse;

/**
 * 数据集测试类
 */
class DatasetListTest extends TestCase
{
    /**
     * @throws ApiException
     */
    public function testListNoParams(): void
    {
        // 获取数据集列表
        $res = $this->client->datasets()->list();

        // 断言返回类型
        $this->assertInstanceOf(DatasetListResponse::class, $res);
        $this->assertNotNull($res->paginator);

        // 验证分页参数
        $this->assertEquals(1, $res->paginator->page);
        $this->assertEquals(20, $res->paginator->limit);
        $this->assertIsInt($res->paginator->total);
        $this->assertIsBool($res->paginator->hasMore);
    }

    /**
     * 测试自定义参数设置
     *
     * 使用自定义的 page 和 limit 参数，验证返回结果是否符合预期
     *
     * @return void
     * @throws ApiException
     */
    public function testListParams(): void
    {
        // 获取数据集列表
        $res = $this->client->datasets()->list(2, 5);

        // 断言返回类型
        $this->assertInstanceOf(DatasetListResponse::class, $res);
        $this->assertNotNull($res->paginator);

        // 验证分页参数
        $this->assertEquals(2, $res->paginator->page);
        $this->assertEquals(5, $res->paginator->limit);
        $this->assertIsInt($res->paginator->total);
        $this->assertIsBool($res->paginator->hasMore);
    }
}
