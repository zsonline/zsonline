window.ZS = {
  ...window.ZS,
  Comments: {
    toggleReplyForm: (element) => {
      const reply = element?.closest(".comments__reply");
      const replyForm = reply?.querySelector(".comments__reply__form");

      const isVisible = replyForm.classList.toggle(
        "comments__reply__form--visible",
      );

      if (isVisible) {
        reply.classList.add("comments__replies--indent");
      } else if (!reply.classList.contains("comments__reply--replies")) {
        reply.classList.remove("comments__replies--indent");
      }
    },
  },
};
