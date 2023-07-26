var express = require("express");
var mongoose = require("mongoose");
const config = require("./config/database");
var bodyparser = require("body-parser");
var cors = require("cors");
var config1 = require("./config/app-config.json");
const path = require("path");

const carRoute = require("./routes/car");

var app = express();

mongoose.connect(config.database, {
  useNewUrlParser: true,
});

mongoose.connection.on("connected", () => {
  console.log("DB is live > " + config.database);
});

mongoose.connection.on("error", (err) => {
  console.log("DB conn. failed : " + err);
});

const PORT = process.env.PORT || config1.port;

app.use(function (req, res, next) {
  res.header("Access-Control-Allow-Origin", "*");
  res.header(
    "Access-Control-Allow-Headers",
    "Origin, X-Requested-With, Content-Type, Accept"
  );
  next();
});
app.use(cors());

app.use(bodyparser.json());

app.use(express.static(path.join(__dirname, "uploads")));

app.use("/car", carRoute);
app.get("/*", function (req, res) {
  res.sendFile(path.join(__dirname, "public", "index.html"));
});

app.listen(PORT, () => {
  console.log("server started at port" + PORT);
});