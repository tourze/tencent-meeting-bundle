<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Tourze\TencentMeetingBundle\Exception\SignatureException;

/**
 * 签名服务
 *
 * 实现TC3-HMAC-SHA256签名算法，为腾讯会议API调用提供认证支持
 */
class SignatureService
{
    /**
     * 生成签名
     *
     * @param array<string, mixed> $params 请求参数
     * @param string $secretKey 密钥
     * @return string 签名
     */
    public function generateSignature(array $params, string $secretKey): string
    {
        return $this->generateTC3Signature($params, $secretKey);
    }

    /**
     * 生成TC3-HMAC-SHA256签名
     *
     * @param array<string, mixed> $params 请求参数
     * @param string $secretKey 密钥
     * @return string 签名
     */
    public function generateTC3Signature(array $params, string $secretKey): string
    {
        // 1. 参数排序
        ksort($params);

        // 2. 构造待签名字符串
        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // 3. 计算签名
        $stringToSign = $queryString;

        return hash_hmac('sha256', $stringToSign, $secretKey);
    }

    /**
     * 验证签名
     *
     * @param array<string, mixed> $params 请求参数
     * @param string $secretKey 密钥
     * @param string $signature 待验证的签名
     * @return bool 验证是否通过
     */
    public function verifySignature(array $params, string $secretKey, string $signature): bool
    {
        try {
            $computedSignature = $this->generateSignature($params, $secretKey);

            return hash_equals($computedSignature, $signature);
        } catch (\Throwable $e) {
            throw new SignatureException('签名验证失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 生成标准TC3签名（完整的腾讯云TC3签名算法）
     *
     * @param string $method HTTP方法
     * @param string $host 请求域名
     * @param string $path 请求路径
     * @param array<string, mixed> $queryParams 查询参数
     * @param array<string, mixed> $headers 请求头
     * @param string $body 请求体
     * @param string $secretKey 密钥
     * @return string 签名
     */
    public function generateStandardTC3Signature(
        string $method,
        string $host,
        string $path,
        array $queryParams,
        array $headers,
        string $body,
        string $secretKey,
    ): string {
        // 1. 构造规范请求
        $canonicalRequest = $this->buildCanonicalRequest($method, $path, $queryParams, $headers, $body);

        // 2. 构造待签字符串
        $stringToSign = $this->buildStringToSign($canonicalRequest);

        // 3. 计算签名
        $signingKey = $this->getSigningKey($secretKey);

        return hash_hmac('sha256', $stringToSign, $signingKey);
    }

    /**
     * 构造规范请求
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $headers
     */
    private function buildCanonicalRequest(string $method, string $path, array $queryParams, array $headers, string $body): string
    {
        $lines = [];

        // HTTP方法
        $lines[] = strtoupper($method);

        // 规范URI
        $lines[] = $path;

        // 规范查询字符串
        $lines[] = $this->buildCanonicalQueryString($queryParams);

        // 规范头
        $lines[] = $this->buildCanonicalHeaders($headers);

        // 签名头列表
        $lines[] = $this->buildSignedHeaders($headers);

        // 请求体的哈希值
        $lines[] = hash('sha256', $body);

        return implode("\n", $lines) . "\n";
    }

    /**
     * 构造规范查询字符串
     * @param array<string, mixed> $params
     */
    private function buildCanonicalQueryString(array $params): string
    {
        if ([] === $params) {
            return '';
        }

        ksort($params);
        $pairs = [];

        foreach ($params as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            $pairs[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
        }

        return implode('&', $pairs);
    }

    /**
     * 构造规范头
     * @param array<string, mixed> $headers
     */
    private function buildCanonicalHeaders(array $headers): string
    {
        $canonicalHeaders = [];

        foreach ($headers as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            $lowerKey = strtolower((string) $key);
            $trimmedValue = trim((string) $value);
            $canonicalHeaders[] = $lowerKey . ':' . $trimmedValue;
        }

        sort($canonicalHeaders);

        return implode("\n", $canonicalHeaders) . "\n";
    }

    /**
     * 构造签名头列表
     * @param array<string, mixed> $headers
     */
    private function buildSignedHeaders(array $headers): string
    {
        $signedHeaders = [];

        foreach (array_keys($headers) as $key) {
            $signedHeaders[] = strtolower($key);
        }

        sort($signedHeaders);

        return implode(';', $signedHeaders);
    }

    /**
     * 构造待签字符串
     */
    private function buildStringToSign(string $canonicalRequest): string
    {
        $algorithm = 'TC3-HMAC-SHA256';
        $hashedCanonicalRequest = hash('sha256', $canonicalRequest);

        return $algorithm . "\n" . $hashedCanonicalRequest;
    }

    /**
     * 获取签名密钥
     */
    private function getSigningKey(string $secretKey): string
    {
        $dateKey = hash_hmac('sha256', date('Y-m-d'), 'TC3' . $secretKey, true);
        $serviceKey = hash_hmac('sha256', 'meeting', $dateKey, true);

        return hash_hmac('sha256', 'tc3_request', $serviceKey, true);
    }

    /**
     * 验证标准TC3签名
     */
    /**
     * @param array<string, string> $queryParams
     * @param array<string, string> $headers
     */
    public function verifyStandardTC3Signature(
        string $method,
        string $host,
        string $path,
        array $queryParams,
        array $headers,
        string $body,
        string $secretKey,
        string $signature,
    ): bool {
        try {
            $computedSignature = $this->generateStandardTC3Signature(
                $method,
                $host,
                $path,
                $queryParams,
                $headers,
                $body,
                $secretKey
            );

            return hash_equals($computedSignature, $signature);
        } catch (\Throwable $e) {
            throw new SignatureException('标准TC3签名验证失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 生成授权头
     */
    /**
     * @param array<string, string> $queryParams
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public function generateAuthorizationHeader(
        string $secretId,
        string $secretKey,
        string $method,
        string $host,
        string $path,
        array $queryParams,
        array $headers,
        string $body,
    ): array {
        // 生成签名
        $signature = $this->generateStandardTC3Signature(
            $method,
            $host,
            $path,
            $queryParams,
            $headers,
            $body,
            $secretKey
        );

        // 构造签名头列表
        $signedHeaders = $this->buildSignedHeaders($headers);

        // 构造授权头
        $authorizationHeader = sprintf(
            'TC3-HMAC-SHA256 Credential=%s/%s/tc3_request, SignedHeaders=%s, Signature=%s',
            $secretId,
            date('Y-m-d'),
            $signedHeaders,
            $signature
        );

        return [
            'Authorization' => $authorizationHeader,
            'X-TC-Action' => $queryParams['Action'] ?? '',
            'X-TC-Version' => $queryParams['Version'] ?? '',
            'X-TC-Timestamp' => $queryParams['Timestamp'] ?? '',
            'X-TC-Nonce' => $queryParams['Nonce'] ?? '',
        ];
    }
}
