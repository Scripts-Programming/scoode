const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

const app = express();
const port = 3000;

app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public'))); // מגיש קבצים סטטיים מהתיקייה 'public'

const newsFilePath = path.join(__dirname, 'news.json');

// פונקציה לקריאת מבזקים
function getNews() {
    try {
        const data = fs.readFileSync(newsFilePath, 'utf8');
        return JSON.parse(data);
    } catch (error) {
        return []; // אם הקובץ לא קיים או שיש שגיאה, מחזירים מערך ריק
    }
}

// פונקציה לכתיבת מבזקים
function saveNews(news) {
    fs.writeFileSync(newsFilePath, JSON.stringify(news, null, 2), 'utf8');
}

// נקודת קצה לקבלת כל המבזקים
app.get('/api/news', (req, res) => {
    const news = getNews();
    res.json(news);
});

// נקודת קצה להוספת מבזק חדש
app.post('/api/news', (req, res) => {
    const { title, content } = req.body;
    if (!title || !content) {
        return res.status(400).json({ message: 'כותרת ותוכן הם שדות חובה.' });
    }

    const news = getNews();
    const newId = news.length > 0 ? Math.max(...news.map(n => n.id)) + 1 : 1;
    const newNewsItem = { id: newId, title, content, date: new Date().toISOString() };
    news.unshift(newNewsItem); // הוספה להתחלה כדי שהחדשים יופיעו ראשונים
    saveNews(news);
    res.status(201).json(newNewsItem);
});

app.listen(port, () => {
    console.log(`שרת פועל בכתובת http://localhost:${port}`);
});