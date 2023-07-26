const express = require("express");
const router = express.Router();
var request = require("request");
var axios = require("axios");
var mongoose = require("mongoose");
var config = require("../config/app-config.json");
const multer = require("multer");
const carWrapper = require("../config/car-wrapper");
const CircularJSON = require("circular-json");

// const { uploadImage } = require("../config/s3");
var storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, "uploads/");
  },
  filename: function (req, file, cb) {
    cb(null, Date.now() + "-" + file.originalname);
  },
});

var filFilter = (req, file, cb) => {
  if (
    file.mimetype === "image/jpg" ||
    file.mimetype === "image/png" ||
    file.mimetype === "image/jpeg"
  ) {
    cb(null, true);
  } else {
    cb(null, false);
  }
};

var upload = multer({
  storage: storage,
  // dest: 'uploads/',
  limits: {
    fileSize: 1024 * 1024 * 10,
  },
  fileFilter: filFilter,
});
const {
  getFileStream
} = require("../config/s3");

const Car = require("../models/car");
const carPhotos = require("../models/car-photos");
const user = require("../models/user");
const car = require("../../communityApiService/models/car");
const {
  response
} = require("express");

router.post("/addCar", upload.single("car_image"), (req, res, next) => {
  let vendor = req.body.vendor;
  let query = {
    vin: req.body.vin
  };
  let errors = [];
  if (!vendor) {
    errors.push({
      message: "Please Add A Vendor",
    });
    res.json({
      success: false,
      errors: errors
    });
  }
  if (vendor === "inverse") {
    let {
      carModel,
      make,
      license,
      vin,
      duration,
      user_id,
      can_types,
      car_model_uuid,
      car_manufacturer_uuid,
      vendor,
    } = req.body;

    let car = req.body;
    // console.log(car)
    if (!carModel) {
      errors.push({
        message: "Please Add A Model",
      });
    }
    if (!make) {
      errors.push({
        message: "Please Add A Make",
      });
    }
    if (!can_types) {
      errors.push({
        message: "Please Add Can Types",
      });
    }
    if (!car_model_uuid) {
      errors.push({
        message: "Please Add Model Uuid Of The Car",
      });
    }
    if (!car_manufacturer_uuid) {
      errors.push({
        message: "Please Add Manufacturer Uuid Of The Car",
      });
    }
    if (!license) {
      errors.push({
        message: "Please Add A License",
      });
    }
    if (!vin) {
      errors.push({
        message: "Please Add a Vin",
      });
    }
    if (!user_id) {
      errors.push({
        message: "Please Add A User Id",
      });
    }
    if (errors.length > 0) {
      res.json({
        success: false,
        errors: errors,
      });
    } else {
      try {
        carWrapper.findCarByVIN(query, (err, carFound) => {
          if (err) {
            errors.push({
              message: err,
            });
            res.json({
              success: false,
              errors: errors
            });
          } else {
            if (carFound == "") {
              car.car_image = req.file.filename;

              carWrapper.addCar(car, (err, result) => {
                if (err) {
                  errors.push({
                    message: err,
                  });
                  res.json({
                    success: false,
                    errors: errors
                  });
                } else {
                  res.json({
                    success: true,
                    message: "Car added successfully",
                    car: result,
                  });
                }
              });
            } else {
              errors.push({
                message: "Vin already registered",
              });
              res.json({
                success: false,
                errors: errors
              });
            }
          }
        });
      } catch (ex) {
        errors.push({
          message: ex,
        });
        res.json({
          success: false,
          errors: errors
        });
      }
    }
  } else if (vendor === "NIO") {
    let {
      carModel,
      make,
      license,
      vin,
      duration,
      user_id,
      vendor
    } = req.body;

    let car = req.body;
    // console.log(car)
    if (!carModel) {
      errors.push({
        message: "Please Add A Model",
      });
    }
    if (!make) {
      errors.push({
        message: "Please Add A Make",
      });
    }
    if (!license) {
      errors.push({
        message: "Please Add A License",
      });
    }
    if (!vin) {
      errors.push({
        message: "Please Add a Vin",
      });
    }
    if (!user_id) {
      errors.push({
        message: "Please Add A User Id",
      });
    }
    if (errors.length > 0) {
      res.json({
        success: false,
        errors: errors,
      });
    } else {
      try {
        carWrapper.findCarByVIN(query, (err, carFound) => {
          if (err) {
            errors.push({
              message: err,
            });
            res.json({
              success: false,
              errors: errors
            });
          } else {
            if (carFound == "") {
              car.car_image = req.file.filename;
              carWrapper.addNIOCar(car, (err, result) => {
                if (err) {
                  errors.push({
                    message: err,
                  });
                  res.json({
                    success: false,
                    errors: errors
                  });
                } else {
                  res.json({
                    success: true,
                    message: "Car added successfully",
                    car: result,
                  });
                }
              });
            } else {
              errors.push({
                message: "Vin already registered",
              });
              res.json({
                success: false,
                errors: errors
              });
            }
          }
        });
      } catch (ex) {
        errors.push({
          message: ex,
        });
        res.json({
          success: false,
          errors: errors
        });
      }
    }
  }
});

router.get("/getAllPendingCars", (req, res, next) => {
  let query = {
    status: false
  };
  let errors = [];
  try {
    carWrapper.findPendingCars(query, (err, veh) => {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: veh
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getAllCars", (req, res, next) => {
  let errors = [];
  try {
    carWrapper.findAllCars(function (err, veh) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: veh
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getTotalCars", (req, res, next) => {
  let errors = [];
  try {
    carWrapper.findAllCars(function (err, veh) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: veh.length
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getAllApprovedCars", (req, res, next) => {
  let errors = [];
  let query = {
    status: true
  };
  try {
    carWrapper.findApprovedCars(query, function (err, veh) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: veh
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getCars:user", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      user_id: req.params.user
    };
    carWrapper.findCarsOfUser(query, function (err, vehs) {
      if (err) {
        errors.push({
          message: err,
        });
        console.log(err);
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: vehs
        });
      }
    });
  } catch (ex) {
    console.log(ex, "exception");
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.patch("/deleteCar:id", (req, res, next) => {
  let errors = [];
  if (!req.params.id) {
    errors.push({
      message: "Id is missing",
    });
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      let query = {
        _id: req.params.id
      };
      carWrapper.deleteCar(query, function (err, veh) {
        if (err) {
          errors.push({
            message: err,
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Car removed successfully"
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errors
      });
    }
  }
});

router.get("/getPendingCars:user", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      user_id: req.params.user
    };
    carWrapper.findPendingCarsOfUser(query, function (err, vehs) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: vehs
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getApprovedCars:user", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      user_id: req.params.user
    };
    carWrapper.findApprovedCarsOfUser(query, function (err, vehs) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: vehs
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.patch("/approveCar", (req, res, next) => {
  // let errors=[];
  let query = {
    vin: req.body.vin,
    status: true,
  };
  let errors = [];
  if (!req.body.vin) {
    errors.push({
      message: "Please Add VIN",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      carWrapper.updateCarStatus(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error approving vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Vehicle approved successfully"
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.patch("/updateSharing", (req, res, next) => {
  // let errors=[];
  let query = {
    id: req.body.id,
    status: req.body.status,
  };
  let errors = [];
  if (!req.body.id) {
    errors.push({
      message: "Please Add Car ID",
    });
  }
  if (req.body.status == "") {
    errors.push({
      message: "Please Add Status",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      carWrapper.updateSharingStatus(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Vehicle updated successfully"
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.patch("/attachQnr", (req, res, next) => {
  let query = {
    _id: req.body.carId,
    qnr: req.body.qnr,
    status: true,
  };
  let errors = [];
  if (!req.body.carId) {
    errors.push({
      message: "Please Send Id Of Car",
    });
  }
  if (!req.body.qnr) {
    errors.push({
      message: "Please send a qnr number",
    });
  }
  if (query.qnr.length < 16 || query.qnr.length > 16) {
    errors.push({
      message: "Invalid Qnr",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      Car.find({
        qnr: req.body.qnr
      }, (err, result) => {
        if (err) {
          errors.push({
            message: err,
          });
          res.json({
            success: false,
            errors: errors,
          });
        }
        if (result == "") {
          console.log("hello");
          carWrapper.updateQnr(query, function (err, result) {
            if (err) {
              errors.push({
                message: "Error updating vehicle",
              });
              res.json({
                success: false,
                errors: errors
              });
            } else {
              res.json({
                success: true,
                message: "Vehicle updated successfully",
              });
            }
          });
        } else {
          let i = 0;
          for (i = 0; i < result.length; i++) {
            Car.updateOne({
                _id: result[i]._id
              }, {
                qnr: "",
                status: false
              },
              function (err, result) {
                console.log("heyyy");
                if (err) {
                  errors.push({
                    message: "Error updating vehicle",
                  });
                  res.json({
                    success: false,
                    errors: errors
                  });
                } else {
                  carWrapper.updateQnr(query, function (err, result) {
                    if (err) {
                      errors.push({
                        message: "Error updating vehicle",
                      });
                      res.json({
                        success: false,
                        errors: errors
                      });
                    } else {
                      res.json({
                        success: true,
                        message: "Vehicle updated successfully",
                      });
                    }
                  });
                }
              }
            );
          }
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errors
      });
    }
  }
});

router.patch("/attachDeviceId", (req, res, next) => {
  let query = {
    _id: req.body.carId,
    deviceId: req.body.qnr,
    status: true,
  };
  let errors = [];
  if (!req.body.carId) {
    errors.push({
      message: "Please Send Id Of Car",
    });
  }
  if (!req.body.qnr) {
    errors.push({
      message: "Please send device ID",
    });
  }
  // if (query.qnr.length < 16 || query.qnr.length > 16) {
  //   errors.push({
  //     message: "Invalid Qnr",
  //   });
  // }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      carWrapper.updateDeviceId(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Vehicle updated successfully",
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errors
      });
    }
  }
});

router.get("/healthcheck", (req, res, next) => {
  res.json();
});

router.patch("/setPreference", (req, res, next) => {
  console.log(req.body);
  // let errors=[];
  let errors = [];
  if (!req.body.vin) {
    errors.push({
      message: "Please Add VIN",
    });
  }
  if (!req.body.days) {
    errors.push({
      message: "Please Add Days",
    });
  }
  if (!req.body.fromTime) {
    errors.push({
      message: "Please Add From",
    });
  }
  if (!req.body.toTime) {
    errors.push({
      message: "Please Add To",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    let query = {
      vin: req.body.vin,
      preferred_days: req.body.days,
      preferred_time_from: req.body.fromTime.split(":")[0],
      preferred_time_to: req.body.toTime.split(":")[0],
      bookable: req.body.bookable,
    };
    try {
      carWrapper.updateCarPreference(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          // if (result.nModified == 0) {
          //     errors.push({
          //         message: 'VIN is invalid'
          //     });
          //     res.json({ success: false, errors: errors })
          // } else {
          res.json({
            result: result,
            success: true,
            message: "Vehicle updated successfully",
          });
          // }
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.post("/getBookableCars", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      user: req.body.user,
      lat: req.body.lat,
      lon: req.body.lon,
      time: req.body.time,
      day: req.body.day,
      community_cars: req.body.community_cars,
    };
    carWrapper.findBookableCars(query, function (err, vehs) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: vehs
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.patch("/updateBooking", (req, res, next) => {
  // let errors=[];
  let query = {
    id: req.body.car_id,
    status: req.body.status,
  };
  let errors = [];
  if (!req.body.car_id) {
    errors.push({
      message: "Please Add Car ID",
    });
  }
  if (req.body.status == undefined || req.body.status == null) {
    errors.push({
      message: "Please Add Status",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      carWrapper.updateBookingStatus(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Vehicle updated successfully"
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.post("/getCarsForSchedule", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      user: req.body.user,
      lat: req.body.lat,
      lon: req.body.lon,
      time_from: req.body.time_from,
      time_to: req.body.time_to,
      days: req.body.days,
      community_cars: req.body.community_cars,
    };
    carWrapper.findBookableCarsForSchedule(query, function (err, vehs) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          cars: vehs
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/getCar:id", (req, res, next) => {
  let errors = [];
  try {
    let query = {
      _id: req.params.id
    };
    carWrapper.findCar(query, function (err, veh) {
      if (err) {
        errors.push({
          message: err,
        });
        res.json({
          success: false,
          errors: errors
        });
      } else {
        res.json({
          success: true,
          car: veh
        });
      }
    });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.patch("/updateCarPhotos", (req, res, next) => {
  // let errors=[];
  let query = {
    id: req.body.car_id,
    car_photos: req.body.car_photos,
  };
  let errors = [];
  if (!req.body.car_id) {
    errors.push({
      message: "Please Add Car ID",
    });
  }
  if (!req.body.car_photos) {
    errors.push({
      message: "Please Add Car Photos ID",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    try {
      carWrapper.updateCarPhotos(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            success: true,
            message: "Vehicle updated successfully"
          });
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.get("/getImage:key", (req, res, next) => {
  let errors = [];
  try {
    const key = req.params.key;
    const readStream = getFileStream(key);
    readStream.pipe(res);
    // res.json({ success: true, readStream: readStream });
  } catch (ex) {
    errors.push({
      message: ex,
    });
    res.json({
      success: false,
      errors: errors
    });
  }
});

router.get("/setDeletedFalse", function (req, res) {
  carWrapper.setDeletedFalse(function (err, result) {
    if (err) {
      errors.push({
        message: "Error updating vehicle",
        err,
      });
      res.json({
        success: false,
        errors: errors
      });
    } else {
      res.json({
        success: true,
        message: "Vehicles updated successfully"
      });
    }
  });
});

router.patch("/setBookable", (req, res, next) => {
  console.log(req.body);
  // let errors=[];
  let errors = [];
  if (!req.body.car) {
    errors.push({
      message: "Please Add car",
    });
  }
  if (errors.length > 0) {
    res.json({
      success: false,
      errors: errors,
    });
  } else {
    let query = {
      car: req.body.car,
      bookable: req.body.bookable,
    };
    try {
      carWrapper.updateBookable(query, function (err, result) {
        if (err) {
          errors.push({
            message: "Error updating vehicle",
          });
          res.json({
            success: false,
            errors: errors
          });
        } else {
          res.json({
            result: result,
            success: true,
            message: "Vehicle updated successfully",
          });
          // }
        }
      });
    } catch (ex) {
      errors.push({
        message: ex,
      });
      res.json({
        success: false,
        errors: errrors
      });
    }
  }
});

router.post("/getVINFromLicensePlate", (req, res) => {
  let {
    license_plate
  } = req.body;
  let errors = [];
  if (!license_plate || license_plate === undefined) {
    errors.push({
      message: "Please add the license plate",
    });
    res.json({
      success: false,
      errors: errors
    });
  } else {
    axios
      .get(config.getVINURL + license_plate)
      .then((response) => {
        let result = CircularJSON.stringify(response);
        result = JSON.parse(result);
        res.json({
          success: true,
          VIN: result.data.understellsnummer
        });
      })
      .catch((err) => {
        errors.push({
          message: "No car found with this license plate",
        });
        res.json({
          success: false,
          errors: errors
        });
      });
  }
});

module.exports = router;