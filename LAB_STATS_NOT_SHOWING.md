# 🚨 Laboratory Stats Not Showing - Complete Fix Guide

## **Problem**: Laboratory Management stats showing 0 or not displaying

## **🔍 Most Common Causes & Solutions**

---

### **1. CORS Issue (Most Likely)**
**Problem**: Frontend can't connect to backend due to CORS policy
**✅ FIXED**: Added CORS middleware to Laravel 11

**Verification**:
- Open browser dev tools (F12)
- Check Console for CORS errors
- Look for "Access-Control-Allow-Origin" errors

**Solution Applied**:
```php
// bootstrap/app.php - CORS middleware added
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

---

### **2. Laravel Server Not Running**
**Problem**: Backend server not accessible
**Check**: `http://127.0.0.1:8000/api/laboratories/stats`

**Solution**:
```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

---

### **3. Database Connection Issue**
**Problem**: Laravel can't connect to database
**Check**: `.env` file configuration

**Solution**:
```bash
# Test database connection
php artisan tinker
> App\Models\Laboratory::count()
```

---

### **4. Frontend API Base URL Issue**
**Problem**: Wrong API URL in frontend
**Check**: `frontend/src/views/LaboratoryManagement.vue`

**Current Setting**:
```javascript
const apiBase = 'http://localhost:8000/api'
```

---

### **5. Authentication Token Issue**
**Problem**: API requires authentication but token missing
**Check**: Browser localStorage for 'auth_token'

**Solution**: The API should work without auth for stats endpoints

---

## **🧪 Step-by-Step Troubleshooting**

### **Step 1: Test Backend Directly**
```bash
# Test API endpoints directly
curl http://127.0.0.1:8000/api/laboratories/stats
```

**Expected Response**:
```json
{
    "total_labs": 6,
    "total_pcs": 16,
    "deployed": 10,
    "under_repair": 2,
    "available": 4
}
```

### **Step 2: Test Frontend Connection**
Open `quick-test.html` in browser:
```
file:///c:/Users/Lenovo/Desktop/misd-inventory-system/quick-test.html
```

### **Step 3: Check Browser Console**
1. Open Laboratory Management page
2. Press F12 → Console tab
3. Look for errors:
   - CORS errors
   - Network errors
   - JavaScript errors

### **Step 4: Check Network Tab**
1. F12 → Network tab
2. Refresh Laboratory Management page
3. Look for failed API calls to:
   - `/api/laboratories`
   - `/api/laboratories/stats`
   - `/api/laboratory-inventory/under-repair`

---

## **🔧 Quick Fixes Applied**

### **1. CORS Configuration Added**
- ✅ Created `config/cors.php`
- ✅ Added CORS middleware to `bootstrap/app.php`
- ✅ Cleared configuration cache

### **2. SQL Reserved Keyword Fixed**
- ✅ Fixed `condition` field queries with backticks
- ✅ Applied to both LaboratoryController and LaboratoryInventoryController

### **3. Frontend Data Structure Fixed**
- ✅ Proper API response handling
- ✅ Added debug logging
- ✅ Fixed data mapping

---

## **📋 Verification Checklist**

### **Backend Verification**
- [ ] Laravel server running on port 8000
- [ ] CORS middleware configured
- [ ] Database connection working
- [ ] API endpoints responding correctly
- [ ] No SQL errors in logs

### **Frontend Verification**
- [ ] No CORS errors in console
- [ ] API calls successful in Network tab
- [ ] Stats data populated in Vue components
- [ ] UI showing actual numbers instead of 0

### **Data Verification**
- [ ] 6 laboratories in database
- [ ] 16 PCs in laboratory inventory
- [ ] Mixed status distribution (deployed, repair, available)

---

## **🚀 Immediate Actions**

### **1. Restart Everything**
```bash
# Stop current server (Ctrl+C)
# Clear caches
php artisan config:clear
php artisan cache:clear

# Restart server
php artisan serve --host=127.0.0.1 --port=8000
```

### **2. Test with Debug Files**
- Open `debug-lab-stats.html` for detailed testing
- Open `quick-test.html` for simple API test
- Check browser console for errors

### **3. Verify Frontend**
- Navigate to Laboratory Management
- Check if stats show actual numbers
- Look for loading indicators or errors

---

## **🎯 Expected Results**

After fixes, you should see:
```
Total Labs: 6
Total PCs: 16  
Deployed: 10
Under Repair: 2
Available: 4
```

Instead of all 0 values.

---

## **📞 If Still Not Working**

### **Advanced Debugging**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Test with Postman/Insomnia
3. Verify database seeders ran correctly
4. Check network firewall/proxy settings

### **Last Resort**
1. Clear browser cache completely
2. Try different browser
3. Check if antivirus blocking connections
4. Verify no conflicting processes on port 8000

---

**Status**: ✅ All major fixes applied
**Next**: Test the system and verify stats are showing correctly
