import { locale } from 'dayjs';
import 'dayjs/locale/zh-cn';
import 'dayjs/locale/en';

/**
 * Set dayjs locale
 *
 */
export function setDayjsLocale() {
  const localMap = {
    'zh-CN': 'zh-cn'
  } satisfies Record<App.I18n.LangType, string>;

  const l = 'zh-CN';

  locale(localMap[l]);
}
