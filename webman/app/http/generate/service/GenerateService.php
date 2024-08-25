<?php

namespace app\http\generate\service;

/**
 * 生成代码服务
 * @package app\http\generate\service
 * Class GenerateService
 */
class GenerateService
{


    /**
     * 生成代码
     * @param array $params
     * @return bool
     */
    public static function generate(array $params): bool
    {
        try {
            // 模块名
            $moduleName = strtolower($params['moduleName']);
            // 数据库表名
            $tableName = self::getNoPrefixTableName($params['tableName']);
            // 时间
            $date = date('Y/m/d H:i');
            // 包名
            $packageName = self::getPackageNameContent($params['classDir']);

            // 生成控制器
//            $tmpController = ControllerService::handleController([
//                'moduleName' => $moduleName,
//                'classDir' => $params['classDir'],
//                'tableName' => $tableName,
//                'upperCameName' => $params['upperCameName'],
//                'classComment' => $params['classComment'],
//                'packageName' => $packageName,
//                'methods' => $params['gen']['controller'],
//                'date' => $date
//            ]);
//            var_dump($tmpController);
            // 生成逻辑层
//            $tmpLogic = LogicService::handleLogic([
//                'moduleName' => $moduleName,
//                'classDir' => $params['classDir'],
//                'tableName' => $tableName,
//                'upperCameName' => $params['upperCameName'],
//                'classComment' => $params['classComment'],
//                'packageName' => $packageName,
//                'gen' => $params['gen'],
//                'date' => $date,
//                'PK' => $params['PK'],
//                'fields' => $params['fields'],
//            ]);
//            var_dump($tmpLogic);

            // 生成模型
            $tmpModel = ModelService::handleModel([
                'moduleName' => $moduleName,
                'classDir' => $params['classDir'],
                'tableName' => $tableName,
                'upperCameName' => $params['upperCameName'],
                'classComment' => $params['classComment'],
                'packageName' => $packageName,
                'PK' => $params['PK'],
                'deleteType' => $params['deleteType'],
                'date' => $date
            ]);

            var_dump($tmpModel);
            return true;
        } catch (\Throwable $e) {
            exceptionLog($e);
            return false;
        }
    }


    /**
     * 获取无前缀数据表名
     * @param string $tableName
     * @return string
     */
    private static function getNoPrefixTableName(string $tableName): string
    {
        $prefix = config('database.connections.mysql.prefix');
        $prefixIndex = strpos($tableName, $prefix);
        if ($prefixIndex === false || $prefixIndex !== 0) {
            return $tableName;
        }
        return trim(str_replace($prefix, '', $tableName));
    }


    /**
     * 下划线转驼峰(首字母大写)
     * @param string $underscoreName
     * @param bool $firstCharacterUpper
     * @return string
     */
    private static function underscoreToCamelCase(string $underscoreName, bool $firstCharacterUpper = true): string
    {
        // 将下划线命名法转换为数组
        $parts = explode('_', $underscoreName);

        // 处理每个部分，使其首字母大写
        $parts = array_map('ucfirst', $parts);

        // 将数组合并为字符串
        $result = implode('', $parts);

        // 如果需要首字母大写，转换第一个字符
        if ($firstCharacterUpper) {
            $result[0] = strtoupper($result[0]);
        }
        return $result;
    }


    /**
     * 蛇形转小驼峰
     * @param $snakeCase
     * @return string
     */
    public static function snakeToCamelCase($snakeCase): string
    {
        return lcfirst(str_replace('_', '', ucwords($snakeCase, '_')));
    }


    /**
     * 获取包名
     * @param string $classDir
     * @return string
     */
    private static function getPackageNameContent(string $classDir): string
    {
        return !empty($classDir) ? '\\' . $classDir : '';
    }


    /**
     * 获取模板路径
     * @param string $templateName
     * @return string
     */
    public static function getTemplatePath(string $templateName): string
    {
        return config('env.generate.template_dir') . $templateName . '.stub';
    }


    /**
     * 获取命名空间内容
     * @param string $moduleName // 模块名
     * @param string $classDir // 类目录
     * @param string $upperCameName
     * @param string $component
     * @return string
     */
    public static function getNameSpaceContent(string $moduleName, string $classDir, string $upperCameName, string $component): string
    {
        if (empty($moduleName)) {
            $tplStr = "namespace app\\http";
        } else {
            $tplStr = "namespace app\\http\\$moduleName";
        }
        $lowerComponent = strtolower($component);
        if (empty($classDir)) {
            return $tplStr . "\\$lowerComponent;";
        }
        $ucfComponent = ucfirst($component);
        return $tplStr . "\\$lowerComponent\\" . $classDir . '\\' . $upperCameName . $ucfComponent . ";";
    }

    /**
     * 替换文件数据
     * @param array $needReplace
     * @param array $waitReplace
     * @param string $template
     * @return string
     */
    public static function replaceFileData(array $needReplace, array $waitReplace, string $template): string
    {

        return str_replace($needReplace, $waitReplace, file_get_contents($template));
    }


    /**
     * 设置空额占位符
     * @param $content
     * @param $blankpace
     * @return string
     */
    public static function setBlankSpace($content, $blankpace): string
    {
        $content = explode(PHP_EOL, $content);
        foreach ($content as $line => $text) {
            $content[$line] = $blankpace . $text;
        }
        return (implode(PHP_EOL, $content));
    }
}