import universal from './universal'
import superadmin from './superadmin'
import adminTenant from './admin-tenant'
import instructor from './instructor'
import student from './student'

export default [...universal, ...superadmin, ...adminTenant, ...instructor, ...student]
