const mongoose = require("mongoose");

const carSchema = mongoose.Schema({
  carModel: {
    type: String,
    required: true,
  },
  car_model_uuid: {
    type: String,
    required: false,
  },
  car_manufacturer_uuid: {
    type: String,
    required: false,
  },
  make: {
    type: String,
    required: true,
  },
  can_types: [{
    type: Number,
    required: false,
  }, ],
  car_photos: {
    type: String,
    ref: "carPhotos",
  },
  license: {
    type: String,
    required: true,
  },
  vin: {
    type: String,
    required: true,
    unique: true,
  },
  duration: {
    type: String,
    required: false,
  },
  user_id: {
    type: String,
    ref: "User",
    required: true,
  },
  status: {
    type: Boolean,
    default: false,
  },
  sharing: {
    type: Boolean,
    default: false,
  },
  car_image: {
    type: String,
    required: true,
  },
  preferred_days: [],
  preferred_time_from: {
    type: Number,
    required: false,
  },
  preferred_time_to: {
    type: Number,
    required: false,
  },
  bookable: {
    type: Boolean,
    default: false,
  },
  booked: {
    type: Boolean,
    default: false,
  },
  deleted: {
    type: Boolean,
    default: false,
  },
  qnr: {
    type: String,
    required: false,
    default: "",
  },
  vendor: {
    type: String,
    required: true,
    default: "inverse",
  },
  deviceId: {
    type: String,
    required: false,
    default: "",
  },
  communities: [{
    type: String,
    required: false
  }],
  community_ids: [{
    type: String,
    required: false
  }],
  created_at: {
    type: Date,
    default: Date.now
  },
  updated_at: {
    type: Date,
    default: Date.now
  }
});

const Car = (module.exports = mongoose.model("Car", carSchema));