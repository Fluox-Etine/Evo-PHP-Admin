package logic

import (
	"fmt"
	"generator/internal/types"
)

func DetailLogic(data *types.GenerateType) (string, error) {
	str := fmt.Sprintf(
		"    /**\n"+
			"     * 详情操作\n"+
			"     * @param array $params\n"+
			"     * @return mixed\n"+
			"     */\n"+
			"    public function getDetail(array $params): mixed\n"+
			"    {\n"+
			"        return %s::where([%s::primaryKey => $params['id']])->first();\n"+
			"    }",
		data.ClassTitle+"Model",
		data.ClassTitle+"Model",
	)

	return str, nil
}