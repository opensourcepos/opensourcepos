// live clock
var clock_tick = function clock_tick() {
  setInterval("update_clock();", 1000);
};

// start the clock immediatly
clock_tick();

var update_clock = function update_clock() {
  document.getElementById("clock").innerHTML = moment().format(
    "<?= dateformat_momentjs($this->config->item('dateformat').' '.$this->config->item('timeformat'))?>"
  );
};
