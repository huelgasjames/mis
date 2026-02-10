# Laboratory Management System - Complete Troubleshooting Guide

## 🚀 Quick Start Checklist

### ✅ Backend Setup
- [ ] Laravel server running on `http://127.0.0.1:8000`
- [ ] Database migrations completed
- [ ] Seeders run (LaboratorySeeder + LaboratoryInventorySeeder)
- [ ] API endpoints accessible

### ✅ Frontend Setup  
- [ ] Vue.js development server running
- [ ] Navigation to Laboratory Management working
- [ ] API base URL set to `http://localhost:8000/api`
- [ ] Authentication token present in localStorage

## 🔧 Common Issues & Solutions

### Issue 1: Stats Not Showing (0 values)
**Symptoms**: All statistics show 0 or empty
**Causes**: 
- API endpoints not responding
- SQL reserved keyword error
- Frontend not receiving data

**Solutions**:
```bash
# 1. Check Laravel server
php artisan serve --host=127.0.0.1 --port=8000

# 2. Test API directly
curl http://127.0.0.1:8000/api/laboratories/stats

# 3. Check database
php artisan tinker
> App\Models\Laboratory::count()
> App\Models\LaboratoryInventory::count()
```

### Issue 2: SQL Reserved Keyword Error
**Symptoms**: Database query fails with "condition" syntax error
**Solution**: Fixed by adding backticks around `condition` in queries
```php
// Before (ERROR)
->selectRaw('condition, COUNT(*) as count')

// After (FIXED)  
->selectRaw('`condition`, COUNT(*) as count')
```

### Issue 3: CORS or Network Errors
**Symptoms**: Frontend can't connect to backend
**Solutions**:
```javascript
// Check API base URL in frontend
const apiBase = 'http://localhost:8000/api'

// Verify Laravel server is running
// Check browser console for network errors
```

## 📊 API Endpoints Reference

### Laboratories
- `GET /api/laboratories` - List all labs with counts
- `POST /api/laboratories` - Create new lab
- `GET /api/laboratories/{id}` - Get specific lab
- `PUT /api/laboratories/{id}` - Update lab
- `DELETE /api/laboratories/{id}` - Delete lab
- `GET /api/laboratories/stats` - Get statistics
- `POST /api/laboratories/generate-lab-pc-number` - Generate PC number

### Laboratory Inventory
- `GET /api/laboratory-inventory` - List all PCs
- `POST /api/laboratory-inventory` - Add new PC
- `GET /api/laboratory-inventory/{id}` - Get specific PC
- `PUT /api/laboratory-inventory/{id}` - Update PC
- `DELETE /api/laboratory-inventory/{id}` - Delete PC
- `GET /api/laboratory-inventory/under-repair` - Get PCs under repair
- `POST /api/laboratory-inventory/{id}/start-repair` - Start repair
- `POST /api/laboratory-inventory/{id}/complete-repair` - Complete repair

## 🗄️ Database Schema

### Laboratories Table
```sql
- id (primary)
- name (string, unique)
- code (string, unique) 
- description (text, nullable)
- location (string, nullable)
- capacity (integer)
- supervisor (string, nullable)
- contact_number (string, nullable)
- status (enum: Active, Maintenance, Closed)
- created_at, updated_at
```

### Laboratory Inventory Table
```sql
- id (primary)
- asset_tag (string, unique)
- computer_name (string)
- lab_pc_num (string)
- category (string)
- processor (string)
- motherboard (string, nullable)
- video_card (string, nullable)
- dvd_rom (string, nullable)
- psu (string, nullable)
- ram (string)
- storage (string)
- serial_number (string, nullable)
- status (enum: Deployed, Under Repair, Available, Defective, For Disposal)
- condition (enum: Excellent, Good, Fair, Poor)
- notes (text, nullable)
- laboratory_id (foreign key)
- deployment_date (date, nullable)
- last_maintenance (date, nullable)
- repair_start_date (date, nullable)
- repair_end_date (date, nullable)
- repair_description (string, nullable)
- repaired_by (string, nullable)
- created_at, updated_at
```

## 🧪 Testing Commands

### Database Tests
```bash
# Check data counts
php artisan tinker
> App\Models\Laboratory::count()
> App\Models\LaboratoryInventory::count()
> App\Models\LaboratoryInventory::where('status', 'Under Repair')->count()

# Test API methods
> app('App\Http\Controllers\LaboratoryController')->getStats()
> app('App\Http\Controllers\LaboratoryInventoryController')->getStats()
```

### API Tests
```bash
# Test endpoints directly
curl http://127.0.0.1:8000/api/laboratories
curl http://127.0.0.1:8000/api/laboratories/stats
curl http://127.0.0.1:8000/api/laboratory-inventory/under-repair
```

### Frontend Tests
1. Open browser developer tools
2. Navigate to Laboratory Management
3. Check Console tab for errors
4. Check Network tab for API calls
5. Verify data in Vue DevTools

## 🔄 Data Flow

1. **Frontend Load** → Calls `fetchData()`
2. **API Calls** → Laravel endpoints
3. **Database Queries** → MySQL/MariaDB
4. **Response** → JSON data
5. **Frontend Update** → Reactive Vue components

## 📱 Current Data Status

Based on latest database check:
- **Total Laboratories**: 6
- **Total PCs**: 16
- **Deployed**: 10
- **Under Repair**: 2  
- **Available**: 4
- **Conditions**: 4 Excellent, 10 Good, 1 Fair, 1 Poor

## 🚨 Emergency Fixes

### If Nothing Shows:
1. Restart Laravel server: `php artisan serve`
2. Clear cache: `php artisan cache:clear`
3. Check database connection: `.env` file
4. Verify migrations: `php artisan migrate:status`

### If API Errors:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify CORS settings
3. Test with Postman/Insomnia
4. Check authentication

### If Frontend Errors:
1. Clear browser cache
2. Check console for JavaScript errors
3. Verify Vue.js installation
4. Check router configuration

## 📞 Support Steps

1. **Check Basics**: Server running? Database connected?
2. **Test API**: Can you call endpoints directly?
3. **Check Frontend**: Any console errors?
4. **Verify Data**: Does database have expected data?
5. **Network Issues**: CORS, firewall, proxy?

---

**Last Updated**: Current session
**Status**: All major issues identified and fixed
**Next Steps**: Test frontend integration and user workflow
