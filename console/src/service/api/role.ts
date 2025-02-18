import { request } from '../request';

/** get role list */
export function fetchGetRoleList() {
  return request<Api.Route.UserRoute>({ url: '/role/list' });
}
