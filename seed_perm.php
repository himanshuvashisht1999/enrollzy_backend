Spatie\Permission\Models\Permission::updateOrCreate(['name' => 'lead-activity-logs-browse', 'guard_name' => 'admin']);
$role = Spatie\Permission\Models\Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
if($role) {
    $role->givePermissionTo('lead-activity-logs-browse');
}
echo "Permission seeded\n";
