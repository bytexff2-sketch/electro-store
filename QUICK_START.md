# DEPLOYMENT QUICK START

## 🚂 Railway in 60 Seconds

```
1. Go to railway.app → Sign in with GitHub
2. New Project → Deploy from GitHub → Select electro-store
3. Wait for build (3-5 min)
4. Click "+" → Add MySQL database
5. Copy MySQL credentials (MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)
6. Click web service → Variables → Add 5 variables (DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME)
7. Click Redeploy
8. Get your URL (electro-store-xyz.railway.app)
9. Visit: https://electro-store-xyz.railway.app/setup.php
10. Click "Initialize Database Now" button
11. Done! Site is live 🎉
```

## 🎨 Render in 60 Seconds

```
1. Go to render.com → Sign in with GitHub
2. New → Blueprint → Select electro-store
3. Click Deploy (automatically creates web + database)
4. Wait for deployment (5-10 min)
5. Get your URL (electro-store-xyz.onrender.com)
6. Visit: https://electro-store-xyz.onrender.com/setup.php
7. Click "Initialize Database Now" button
8. Done! Site is live 🎉
```

## ✅ Both Platforms Done?

```
1. Delete setup.php from your project
2. git add . && git commit -m "Remove setup.php"
3. git push origin main
4. Both platforms auto-redeploy (removing the file)
5. Sites are now secure and live!
```

## 📊 What You Have Now

- ✅ Live site on Railway
- ✅ Live site on Render  
- ✅ Auto-scaled databases included
- ✅ Free tier (or paid if you upgrade)
- ✅ Automatic deployments when you push to GitHub

## 🎯 Next Steps

- Add products through admin dashboard
- Invite friends to test your store
- Monitor performance on both platforms
- Scale up if needed (easy upgrade path)

## 💾 Always Remember

```bash
# When you make changes locally:
git add .
git commit -m "Your changes"
git push origin main

# Both Railway AND Render automatically redeploy! 🚀
```

---

**See DEPLOYMENT_GUIDE.md for detailed step-by-step instructions**
