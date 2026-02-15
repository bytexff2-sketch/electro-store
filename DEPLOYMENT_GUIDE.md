# Electronics Store - Deployment Guide

**Complete step-by-step guide for deploying to Railway and Render**

---

## 📋 Quick Checklist

Before starting deployment, ensure you have:
- [x] GitHub account (already done)
- [x] Code pushed to GitHub (already done)
- [ ] Railway account (create if needed)
- [ ] Render account (create if needed)

---

# 🚂 RAILWAY DEPLOYMENT

## Step 1: Create Railway Account
**Time: 2 minutes**

1. Go to https://railway.app/
2. Click **"Login"** button
3. Click **"GitHub"** to sign in with GitHub
4. Authorize Railway to access your GitHub account
5. Verify your email if prompted

✅ **Status: Logged into Railway**

---

## Step 2: Create New Project
**Time: 5 minutes**

1. Click **"New Project"** (top right corner)
2. Select **"Deploy from GitHub"**
3. Search for **`electro-store`** repository
4. Click on your repository to select it
5. Click **"Deploy"** button

⏳ **Railway is now building your project** - Wait 3-5 minutes for the build to complete
- You'll see a progress indicator
- Once complete, you'll see a green checkmark

✅ **Status: Web service deployed**

---

## Step 3: Add MySQL Database
**Time: 3 minutes**

1. In your Railway project dashboard, click the **blue "+"** button (left sidebar)
2. Select **"Database"** from the menu
3. Click **"MySQL"**
4. Railway will create a MySQL instance automatically

⏳ **Wait 1-2 minutes for MySQL to provision**

✅ **Status: MySQL database created**

---

## Step 4: Get Database Credentials (IMPORTANT)
**Time: 2 minutes**

1. Click on the **"MySQL"** service in your project
2. Click the **"Variables"** tab
3. You'll see 5 important variables:

```
MYSQL_HOST = [copy this]
MYSQL_PORT = [copy this]
MYSQL_USER = [copy this]
MYSQL_PASSWORD = [copy this]
MYSQL_DATABASE = [copy this]
```

📝 **Copy these values to a text file for the next step**

Example (your values will be different):
```
MYSQL_HOST = mysql.railway.internal
MYSQL_PORT = 3306
MYSQL_USER = root
MYSQL_PASSWORD = abc123xyz...
MYSQL_DATABASE = railway
```

✅ **Status: Database credentials obtained**

---

## Step 5: Configure Environment Variables
**Time: 2 minutes**

1. Click on your **web service** (the one showing railway.app URL)
2. Click the **"Variables"** tab
3. Click **"New Variable"** and add these exactly:

**Variable 1:**
- Key: `DB_HOST`
- Value: (paste your MYSQL_HOST value)

**Variable 2:**
- Key: `DB_PORT`
- Value: (paste your MYSQL_PORT value)

**Variable 3:**
- Key: `DB_USER`
- Value: (paste your MYSQL_USER value)

**Variable 4:**
- Key: `DB_PASS`
- Value: (paste your MYSQL_PASSWORD value)

**Variable 5:**
- Key: `DB_NAME`
- Value: (paste your MYSQL_DATABASE value)

4. After adding all 5, click **"Redeploy"** button

⏳ **Wait 2-3 minutes for redeploy to complete**

✅ **Status: Environment variables configured**

---

## Step 6: Get Your Live URL
**Time: 1 minute**

1. Click your **web service**
2. Look at the top of the page - you'll see a URL like:
   ```
   https://electro-store-xyz1234.railway.app
   ```

📝 **Copy this URL - this is your live website!**

✅ **Status: Live URL obtained**

---

## Step 7: Initialize Database via Web Interface
**Time: 3 minutes**

This is the easiest part! Your project includes an automatic setup script.

1. In your browser, go to:
   ```
   https://[your-railway-url]/setup.php
   ```
   
   Example: `https://electro-store-xyz1234.railway.app/setup.php`

2. You'll see a beautiful setup page with:
   - ✅ Connection Status (should show green "Connected")
   - Database configuration details
   - A big blue **"🚀 Initialize Database Now"** button

3. Click **"Initialize Database Now"**

⏳ **Wait 30 seconds for the database to initialize**

4. You should see:
   ```
   ✅ Database 'electronics_store' created
   ✅ All tables created successfully
   ✅ Schema imported successfully
   ```

✅ **Status: Database initialized!**

---

## Step 8: Test Your Live Site
**Time: 5 minutes**

1. Go to your Railway URL: `https://electro-store-xyz1234.railway.app`
2. You should see your Electronics Store homepage! 🎉
3. Test these features:
   - [ ] View products page
   - [ ] Click on a product to see details
   - [ ] Add product to cart
   - [ ] Try to register (create account)
   - [ ] Try to login with your new account
   - [ ] View orders page

✅ **Status: Site is LIVE!**

---

## Step 9: Cleanup (IMPORTANT FOR SECURITY)
**Time: 1 minute**

The `setup.php` file should only be used once. For security, remove it:

1. In your local project, delete the file:
   ```
   setup.php
   ```

2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Remove setup.php for security"
   git push origin main
   ```

3. Railway will automatically redeploy (removing the file)

✅ **Status: Security cleanup done**

---

## 🎉 Railway Deployment Complete!

Your site is now live at:
```
https://[your-railway-url].railway.app
```

---

# 🎨 RENDER DEPLOYMENT

**Important: You can deploy to both Railway AND Render. They'll have different URLs but the same database.** (Or use separate databases if you prefer)

---

## Step 1: Create Render Account
**Time: 2 minutes**

1. Go to https://render.com/
2. Click **"Sign Up"** button
3. Choose **"GitHub"** to sign up with GitHub
4. Authorize Render to access your GitHub account
5. Verify your email if prompted

✅ **Status: Logged into Render**

---

## Step 2: Deploy Blueprint (Automatic Setup)
**Time: 10 minutes**

This is the easiest! Render will automatically:
- Create a PHP web service
- Create a MySQL database
- Configure all environment variables

Steps:

1. Click **"New +"** button (top right)
2. Select **"Blueprint"**
3. Select **"Public GitHub"** (or your GitHub account if it's private)
4. Search for `electro-store` repository
5. Click to select it
6. Review the configuration (you should see web service + database listed)
7. Click **"Deploy"** button

⏳ **Wait 5-10 minutes for deployment**

You'll see:
- 🟦 Blue = Still deploying
- 🟩 Green = Service is online

Once both are green, continue to the next step.

✅ **Status: Web service + Database deployed**

---

## Step 3: Get Your Live URL
**Time: 1 minute**

1. In Render dashboard, click on the **web service** (called `electro-store`)
2. At the top, you'll see a URL like:
   ```
   https://electro-store-abcd1234.onrender.com
   ```

📝 **Copy this URL - this is your live website!**

✅ **Status: Live URL obtained**

---

## Step 4: Initialize Database via Web Interface
**Time: 3 minutes**

Same as Railway! Your setup script is included.

1. In your browser, go to:
   ```
   https://[your-render-url]/setup.php
   ```
   
   Example: `https://electro-store-abcd1234.onrender.com/setup.php`

2. Click **"🚀 Initialize Database Now"**

3. Wait for success message:
   ```
   ✅ Database 'electronics_store' created
   ✅ All tables created successfully
   ```

✅ **Status: Database initialized!**

---

## Step 5: Test Your Live Site
**Time: 5 minutes**

1. Go to your Render URL: `https://electro-store-abcd1234.onrender.com`
2. Test the site (same as Railway):
   - [ ] View products
   - [ ] Click on product
   - [ ] Add to cart
   - [ ] Register
   - [ ] Login
   - [ ] View orders

✅ **Status: Site is LIVE!**

---

## Step 6: Cleanup (IMPORTANT)
**Time: 1 minute**

Remove the setup.php file:

1. Delete `setup.php` from your local project
2. Commit and push:
   ```bash
   git add .
   git commit -m "Remove setup.php for security"
   git push origin main
   ```

3. Render will automatically redeploy

✅ **Status: Security cleanup done**

---

## 🎉 Render Deployment Complete!

Your site is now live at:
```
https://[your-render-url].onrender.com
```

---

# 📊 You Now Have Two Live Sites!

| Platform | URL | Database |
|----------|-----|----------|
| Railway | `https://electro-store-xyz1234.railway.app` | MySQL on Railway |
| Render | `https://electro-store-abcd1234.onrender.com` | MySQL on Render |

Both sites are fully functional and independent!

---

# ⚙️ Troubleshooting

## Issue: "Connection Failed" at setup.php

**Problem:** Database credentials are incorrect

**Solution:**
1. Check your environment variables on the platform
2. Verify they match exactly (case-sensitive!)
3. Ensure the database user has CREATE DATABASE privileges
4. Try the setup.php again

---

## Issue: Site loads but shows database errors

**Problem:** Database variables not set correctly, or setup.php wasn't run

**Solution:**
1. Visit `/setup.php` and run initialization again
2. Verify all 5 environment variables are set
3. Check they match exactly with no extra spaces

---

## Issue: Products don't show

**Problem:** Database created but no sample data

**Solution:**
1. This is normal! The database is empty.
2. Add products through the admin panel, or
3. Use the backend script: `php backend/insert-sample-data.php`

---

# 🔐 Security Checklist

After deployment, ensure:

- [ ] Deleted `setup.php` from all sites
- [ ] Database credentials are environment variables (not in code)
- [ ] Admin password is changed from default
- [ ] Force HTTPS is enabled on platform (check settings)
- [ ] Database backups are enabled

---

# 📞 Need Help?

If you get stuck:
1. Check the platform's status/logs (look for "Logs" tab)
2. Verify database credentials match exactly
3. Ensure environment variables are set with no typos
4. Try clearing browser cache and refresh
5. Check the config.php uses getenv() for credentials

---

**Congratulations! Your electronics store is now live on the internet! 🚀**
